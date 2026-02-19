<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\MessageLike;
use App\Models\Settings;
use App\Models\Topic;
use App\Models\TopicSubscription;
use App\Models\User;
use App\Notifications\PrivateMessageNotification;
use App\Notifications\TopicReplyNotification;
use App\Support\TopicAccessRules;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MessageServiceSupport
{
    protected const BLOCKED_ACTION_MESSAGE = 'თქვენი ანგარიში დროებით შეზღუდულია. მოქმედების შესრულება შეუძლებელია.';

    /**
     * Normalize text content (trim + convert empty string to null).
     *
     * @param string|null $content
     * @return string|null
     */
    public function normalizeContent(?string $content): ?string
    {
        $content = is_string($content) ? trim($content) : null;

        return $content === '' ? null : $content;
    }

    /**
     * Keep only valid UploadedFile instances.
     *
     * @param array $files
     * @return array
     */
    public function filterUploadedFiles(array $files): array
    {
        return array_values(
            array_filter($files, fn($file) => $file instanceof UploadedFile)
        );
    }

    /**
     * Ensure the sender is allowed to interact with this conversation.
     *
     * @param Conversation $conversation
     * @param int $senderId
     * @return void
     * @throws AuthorizationException
     */
    public function authorizeConversationAccess(Conversation $conversation, int $senderId): void
    {
        if ($conversation->isTopic()) {
            $topic = $this->ensureTopicConversationVisible($conversation);
            $sender = User::find($senderId);
            if (!($sender instanceof User) || Gate::forUser($sender)->denies('post', $topic)) {
                throw new AuthorizationException('You are not allowed to post in this topic.');
            }

            return;
        }

        if ($conversation->isPrivate()) {
            $sender = User::find($senderId);
            if (!($sender instanceof User) || !$sender->isVerified()) {
                throw new AuthorizationException('Private chat is available only for verified users.');
            }

            $this->ensureUserIsNotBlocked($sender);

            $this->authorizePrivateConversationParticipant($conversation, $senderId);

            $this->ensurePrivateReceiverCanReceive($conversation, $senderId);
        }
    }

    /**
     * Ensure the current user can read messages in the given conversation.
     *
     * @param Conversation $conversation
     * @param int|null $viewerId
     * @return void
     * @throws AuthorizationException
     */
    public function authorizeConversationRead(Conversation $conversation, ?int $viewerId): void
    {
        if ($conversation->isTopic()) {
            $this->ensureTopicConversationVisible($conversation);
            return;
        }

        if ($conversation->isPrivate()) {
            if (!$viewerId) {
                throw new AuthorizationException('You are not allowed to access this private conversation.');
            }

            $this->authorizePrivateConversationParticipant($conversation, $viewerId);
            return;
        }

        throw new AuthorizationException('You are not allowed to access this conversation.');
    }

    /**
     * Register participants for the conversation (including private counterpart).
     *
     * @param Conversation $conversation
     * @param int $senderId
     * @return void
     */
    public function ensureParticipants(Conversation $conversation, int $senderId): void
    {
        if ($conversation->isPrivate()) {
            $this->syncPrivateParticipants($conversation);
            return;
        }

        $this->ensureConversationParticipant($conversation->id, $senderId);
    }

    /**
     * Ensure the topic subscription exists for the active poster.
     *
     * @param int $topicId
     * @param int $userId
     * @return void
     */
    public function ensureSubscription(int $topicId, int $userId): void
    {
        $this->ensureTopicSubscription($topicId, $userId);
    }

    /**
     * Guard message reactions (like/unlike) by conversation type and visibility.
     *
     * @param Message $message
     * @param int $userId
     * @return void
     * @throws AuthorizationException
     */
    public function authorizeMessageReaction(Message $message, int $userId): void
    {
        $reactor = User::find($userId);
        if (!($reactor instanceof User)) {
            throw new AuthorizationException('You are not allowed to react to this message.');
        }

        $this->ensureUserIsNotBlocked($reactor);

        $message->loadMissing('conversation');

        $conversation = $message->conversation;
        if (!$conversation) {
            throw new AuthorizationException('Conversation not found for this message.');
        }

        if ($conversation->isPrivate()) {
            $this->authorizePrivateConversationParticipant($conversation, $userId);
            return;
        }

        if (!$conversation->isTopic()) {
            throw new AuthorizationException('Likes are not available for this message.');
        }

        $this->ensureTopicConversationVisible($conversation);
    }

    /**
     * Ensure the user is one of the direct private conversation members.
     *
     * @param Conversation $conversation
     * @param int $userId
     * @return void
     * @throws AuthorizationException
     */
    protected function authorizePrivateConversationParticipant(Conversation $conversation, int $userId): void
    {
        $isDirectParticipant = in_array($userId, [
            $conversation->direct_user1_id,
            $conversation->direct_user2_id,
        ], true);

        if (!$isDirectParticipant) {
            throw new AuthorizationException('You are not allowed to access this private conversation.');
        }
    }

    /**
     * Validate topic conversation visibility parity with TopicController::show.
     *
     * @param Conversation $conversation
     * @return Topic
     * @throws AuthorizationException
     */
    protected function ensureTopicConversationVisible(Conversation $conversation): Topic
    {
        $conversation->loadMissing('topic.category');

        $topic = $conversation->topic;
        if (!$topic || !TopicAccessRules::canView($topic)) {
            throw new AuthorizationException('You are not allowed to access this topic.');
        }

        return $topic;
    }

    /**
     * Load like counts and liked-by-me map for provided messages.
     *
     * @param mixed $messageIds
     * @param int|null $currentUserId
     * @return array
     */
    public function loadMessageLikes($messageIds, ?int $currentUserId): array
    {
        if ($messageIds->isEmpty()) {
            return [collect(), collect()];
        }

        $likeCounts = MessageLike::query()
            ->whereIn('message_id', $messageIds)
            ->select('message_id', DB::raw('count(*) as like_count'))
            ->groupBy('message_id')
            ->pluck('like_count', 'message_id');

        $likedByMe = collect();

        if ($currentUserId) {
            $likedByMe = MessageLike::query()
                ->whereIn('message_id', $messageIds)
                ->where('user_id', $currentUserId)
                ->pluck('message_id')
                ->flip();
        }

        return [$likeCounts, $likedByMe];
    }

    /**
     * Persist uploaded files as message attachments.
     * Images are optimized; documents stored raw.
     *
     * @param Conversation $conversation
     * @param Message $message
     * @param array $files
     * @return void
     * @throws ValidationException
     */
    public function storeAttachments(Conversation $conversation, Message $message, array $files): void
    {
        if (empty($files)) {
            return;
        }

        $diskName = config('chat.attachments_disk');
        $relativeDir = Conversation::ATTACHMENT_DIR_PREFIX . $conversation->id;

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $originalMime = $file->getMimeType() ?: 'application/octet-stream';
            $isImage = Str::startsWith($originalMime, 'image/');

            try {
                $storedPath = $isImage
                    ? ImageUploadService::handleOptimizedImageUpload(
                        file: $file,
                        destinationPath: $relativeDir,
                        oldFile: null,
                        webpQuality: 80,
                        optimize: true,
                        disk: $diskName
                    )
                    : FileUploadService::handleFileUpload(
                        file: $file,
                        destinationPath: $relativeDir,
                        oldFile: null,
                        disk: $diskName
                    );

                $mime = $isImage ? 'image/webp' : $originalMime;
            } catch (\Throwable) {
                throw ValidationException::withMessages([
                    'attachments' => ['Failed to store one of the attachments.'],
                ]);
            }

            $sizeBytes = Storage::disk($diskName)->exists($storedPath)
                ? Storage::disk($diskName)->size($storedPath)
                : (int) $file->getSize();

            MessageAttachment::create([
                'message_id' => $message->id,
                'attachment_type' => $isImage ? 'image' : 'document',
                'disk' => $diskName,
                'path' => $storedPath,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mime,
                'size_bytes' => $sizeBytes,
            ]);
        }
    }

    /**
     * Notify all topic subscribers about a new reply.
     * Uses deduplication table to prevent double-sends.
     *
     * @param int $senderId
     * @param int $messageId
     * @param int $topicId
     * @return void
     */
    public function notifyTopicSubscribers(int $senderId, int $messageId, int $topicId): void
    {
        $context = [
            'message_id' => $messageId,
            'conversation_id' => null,
            'topic_id' => $topicId,
            'sender_id' => $senderId,
            'receiver_ids' => [],
        ];

        try {
            $topic = Topic::find($topicId);
            $message = Message::withTrashed()->find($messageId);
            $sender = User::find($senderId);

            if (
                !($topic instanceof Topic) ||
                !($message instanceof Message) ||
                !($sender instanceof User)
            ) {
                return;
            }

            $context['conversation_id'] = $message->conversation_id;

            $subscriberIds = TopicSubscription::query()
                ->where('topic_id', $topicId)
                ->where('user_id', '!=', $senderId)
                ->pluck('user_id')
                ->unique()
                ->values()
                ->all();

            if (empty($subscriberIds)) {
                return;
            }

            $subscriberIds = array_values(array_unique(array_map('intval', $subscriberIds)));
            $alreadyDeliveredIds = DB::table('topic_notification_deliveries')
                ->where('message_id', $messageId)
                ->whereIn('user_id', $subscriberIds)
                ->pluck('user_id')
                ->map(static fn($id) => (int) $id)
                ->all();

            $pendingIds = array_values(array_diff($subscriberIds, $alreadyDeliveredIds));
            if (empty($pendingIds)) {
                return;
            }

            $context['receiver_ids'] = $pendingIds;
            $recipients = User::whereIn('id', $pendingIds)
                ->get()
                ->keyBy('id');

            foreach ($pendingIds as $userId) {
                $receiver = $recipients->get($userId);
                if (!($receiver instanceof User)) {
                    continue;
                }

                $perReceiverContext = array_merge($context, [
                    'receiver_ids' => [$userId],
                ]);

                try {
                    Notification::send(
                        $receiver,
                        new TopicReplyNotification($topic, $message, $sender)
                    );
                } catch (\Throwable $exception) {
                    $this->reportNotificationFailure(
                        'Failed to send topic reply notification.',
                        $exception,
                        $perReceiverContext
                    );
                    continue;
                }

                try {
                    DB::table('topic_notification_deliveries')
                        ->insertOrIgnore([
                            'user_id' => $userId,
                            'message_id' => $messageId,
                            'created_at' => now(),
                        ]);
                } catch (\Throwable $exception) {
                    $this->reportNotificationFailure(
                        'Failed to persist topic notification delivery row.',
                        $exception,
                        $perReceiverContext
                    );
                }
            }
        } catch (\Throwable $exception) {
            $this->reportNotificationFailure(
                'Unexpected failure while preparing topic notifications.',
                $exception,
                $context
            );
        }
    }

    /**
     * Notify the private-message receiver after a successful send.
     *
     * @param int $senderId
     * @param int $messageId
     * @param int $conversationId
     * @return void
     */
    public function notifyPrivateReceiver(int $senderId, int $messageId, int $conversationId): void
    {
        $context = [
            'message_id' => $messageId,
            'conversation_id' => $conversationId,
            'topic_id' => null,
            'sender_id' => $senderId,
            'receiver_ids' => [],
        ];

        try {
            $conversation = Conversation::find($conversationId);
            $message = Message::withTrashed()->find($messageId);
            $sender = User::find($senderId);

            if (
                !($conversation instanceof Conversation) ||
                !$conversation->isPrivate() ||
                !($message instanceof Message) ||
                !($sender instanceof User)
            ) {
                return;
            }

            $receiverId = $conversation->direct_user1_id === $senderId
                ? $conversation->direct_user2_id
                : $conversation->direct_user1_id;

            if (!$receiverId) {
                return;
            }

            $context['receiver_ids'] = [$receiverId];

            $receiver = User::find($receiverId);
            if (!($receiver instanceof User)) {
                return;
            }

            try {
                Notification::send(
                    $receiver,
                    new PrivateMessageNotification($conversation, $message, $sender)
                );
            } catch (\Throwable $exception) {
                $this->reportNotificationFailure(
                    'Failed to send private message notification.',
                    $exception,
                    $context
                );
            }
        } catch (\Throwable $exception) {
            $this->reportNotificationFailure(
                'Unexpected failure while preparing private message notification.',
                $exception,
                $context
            );
        }
    }

    /**
     * Enforce that private message receiver has a verified email.
     *
     * @param Conversation $conversation
     * @param int $senderId
     * @return void
     * @throws ValidationException
     */
    public function ensurePrivateReceiverCanReceive(Conversation $conversation, int $senderId): void
    {
        if (!$conversation->isPrivate()) {
            return;
        }

        if (!Settings::shouldEmailVerify()) {
            return;
        }

        $receiverId = $conversation->direct_user1_id === $senderId
            ? $conversation->direct_user2_id
            : $conversation->direct_user1_id;

        if (!$receiverId) {
            throw ValidationException::withMessages([
                'content' => ['მიმოწერა ამ დროისთვის ხელმისაწვდომი არ არის.'],
            ]);
        }

        $receiver = User::query()
            ->select(['id', 'email_verified_at'])
            ->find($receiverId);

        if (!($receiver instanceof User) || is_null($receiver->email_verified_at)) {
            throw ValidationException::withMessages([
                'content' => ['მიმოწერა ამ დროისთვის ხელმისაწვდომი არ არის.'],
            ]);
        }
    }

    protected function reportNotificationFailure(string $message, \Throwable $exception, array $context = []): void
    {
        logger()->error($message, array_merge($context, [
            'exception_class' => $exception::class,
            'exception_message' => $exception->getMessage(),
        ]));

        report($exception);
    }

    protected function ensureConversationParticipant(int $conversationId, int $userId): void
    {
        ConversationParticipant::query()->insertOrIgnore([
            'conversation_id' => $conversationId,
            'user_id' => $userId,
            'joined_at' => now(),
        ]);
    }

    /**
     * Keep private participant mapping equal to direct user pair.
     */
    protected function syncPrivateParticipants(Conversation $conversation): void
    {
        $directUser1Id = $conversation->direct_user1_id ? (int) $conversation->direct_user1_id : null;
        $directUser2Id = $conversation->direct_user2_id ? (int) $conversation->direct_user2_id : null;

        if (!$directUser1Id || !$directUser2Id) {
            return;
        }

        $expectedUserIds = [$directUser1Id, $directUser2Id];

        ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->whereNotIn('user_id', $expectedUserIds)
            ->delete();

        foreach ($expectedUserIds as $userId) {
            $this->ensureConversationParticipant($conversation->id, $userId);
        }
    }

    protected function ensureTopicSubscription(int $topicId, int $userId): void
    {
        TopicSubscription::query()->insertOrIgnore([
            'user_id' => $userId,
            'topic_id' => $topicId,
            'subscribed_at' => now(),
        ]);
    }

    protected function ensureUserIsNotBlocked(User $user): void
    {
        if ($user->is_blocked) {
            throw new AuthorizationException(self::BLOCKED_ACTION_MESSAGE);
        }
    }
}
