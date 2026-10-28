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
use App\Support\ChatAttachmentRules;
use App\Support\TopicAccessRules;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
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
     */
    public function normalizeContent(?string $content): ?string
    {
        $content = is_string($content) ? trim($content) : null;

        return $content === '' ? null : $content;
    }

    /**
     * Keep only valid UploadedFile instances.
     */
    public function filterUploadedFiles(array $files): array
    {
        return array_values(
            array_filter($files, fn ($file) => $file instanceof UploadedFile)
        );
    }

    /**
     * Ensure the sender is allowed to interact with this conversation.
     *
     * @throws AuthorizationException
     */
    public function authorizeConversationAccess(Conversation $conversation, int $senderId): void
    {
        if ($conversation->isTopic()) {
            $topic = $this->ensureTopicConversationVisible($conversation);
            $sender = User::find($senderId);
            if (! ($sender instanceof User) || Gate::forUser($sender)->denies('post', $topic)) {
                throw new AuthorizationException('You are not allowed to post in this topic.');
            }

            return;
        }

        if ($conversation->isPrivate()) {
            $sender = User::find($senderId);
            if (! ($sender instanceof User) || ! $sender->isVerified()) {
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
     * @throws AuthorizationException
     */
    public function authorizeConversationRead(Conversation $conversation, ?int $viewerId): void
    {
        if ($conversation->isTopic()) {
            $this->ensureTopicConversationVisible($conversation);

            return;
        }

        if ($conversation->isPrivate()) {
            if (! $viewerId) {
                throw new AuthorizationException('You are not allowed to access this private conversation.');
            }

            $this->authorizePrivateConversationParticipant($conversation, $viewerId);

            return;
        }

        throw new AuthorizationException('You are not allowed to access this conversation.');
    }

    /**
     * Register participants for the conversation (including private counterpart).
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
     */
    public function ensureSubscription(int $topicId, int $userId): void
    {
        $this->ensureTopicSubscription($topicId, $userId);
    }

    /**
     * Guard message reactions (like/unlike) by conversation type and visibility.
     *
     * @throws AuthorizationException
     */
    public function authorizeMessageReaction(Message $message, int $userId): void
    {
        $reactor = User::find($userId);
        if (! ($reactor instanceof User)) {
            throw new AuthorizationException('You are not allowed to react to this message.');
        }

        $this->ensureUserIsNotBlocked($reactor);

        $message->loadMissing('conversation');

        if ((bool) $message->is_trashed) {
            throw new AuthorizationException('Likes are not available for this message.');
        }

        $conversation = $message->conversation;
        if (! $conversation) {
            throw new AuthorizationException('Conversation not found for this message.');
        }

        if ($conversation->isPrivate()) {
            $this->authorizePrivateConversationParticipant($conversation, $userId);

            return;
        }

        if (! $conversation->isTopic()) {
            throw new AuthorizationException('Likes are not available for this message.');
        }

        $this->ensureTopicConversationVisible($conversation);
    }

    /**
     * Ensure the user is one of the direct private conversation members.
     *
     * @throws AuthorizationException
     */
    protected function authorizePrivateConversationParticipant(Conversation $conversation, int $userId): void
    {
        $isDirectParticipant = in_array($userId, [
            $conversation->direct_user1_id,
            $conversation->direct_user2_id,
        ], true);

        if (! $isDirectParticipant) {
            throw new AuthorizationException('You are not allowed to access this private conversation.');
        }
    }

    /**
     * Validate topic conversation visibility parity with TopicController::show.
     *
     * @throws AuthorizationException
     */
    protected function ensureTopicConversationVisible(Conversation $conversation): Topic
    {
        $conversation->loadMissing('topic.category');

        $topic = $conversation->topic;
        if (! $topic || ! TopicAccessRules::canView($topic)) {
            throw new AuthorizationException('You are not allowed to access this topic.');
        }

        return $topic;
    }

    /**
     * Load like counts and liked-by-me map for provided messages.
     *
     * @param  mixed  $messageIds
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
     * @throws ValidationException
     */
    public function storeAttachments(Conversation $conversation, Message $message, array $files): void
    {
        if (empty($files)) {
            return;
        }

        $diskName = config('chat.attachments_disk');
        $relativeDir = Conversation::ATTACHMENT_DIR_PREFIX.$conversation->id;

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $originalMime = $file->getMimeType() ?: 'application/octet-stream';

            if ($originalMime === 'image/svg+xml') {
                throw ValidationException::withMessages([
                    'attachments' => [ChatAttachmentRules::svgNotAllowedMessage()],
                ]);
            }

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
                    'attachments' => [ChatAttachmentRules::storeFailedMessage()],
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
                ! ($topic instanceof Topic) ||
                ! ($message instanceof Message) ||
                ! ($sender instanceof User)
            ) {
                return;
            }

            $context['conversation_id'] = $message->conversation_id;

            $subscriberQuery = $this->topicSubscriberIdsQuery($senderId, $topicId);
            $chunkSize = min(1000, max(1, (int) config('chat.topic_notification_chunk_size', 500)));
            $lastUserId = 0;

            do {
                $subscriberIds = DB::query()
                    ->fromSub(clone $subscriberQuery, 'topic_subscribers')
                    ->where('user_id', '>', $lastUserId)
                    ->orderBy('user_id')
                    ->limit($chunkSize)
                    ->pluck('user_id')
                    ->map(static fn ($id) => (int) $id)
                    ->all();

                if (empty($subscriberIds)) {
                    break;
                }

                $lastUserId = max($subscriberIds);
                $this->notifyTopicSubscriberChunk(
                    $subscriberIds,
                    $topic,
                    $message,
                    $sender,
                    $context,
                );
            } while (count($subscriberIds) === $chunkSize);
        } catch (\Throwable $exception) {
            $this->reportNotificationFailure(
                'Unexpected failure while preparing topic notifications.',
                $exception,
                $context
            );

            throw $exception;
        }
    }

    protected function topicSubscriberIdsQuery(int $senderId, int $topicId): EloquentBuilder
    {
        $expertTopicSubscribers = DB::table('expert_topic_subscriptions')
            ->join('users', 'users.id', '=', 'expert_topic_subscriptions.user_id')
            ->select('expert_topic_subscriptions.user_id')
            ->where('expert_topic_subscriptions.topic_id', $topicId)
            ->where('expert_topic_subscriptions.user_id', '!=', $senderId)
            ->where('users.is_expert', true)
            ->whereNull('users.deleted_at');

        $allTopicExpertSubscribers = User::query()
            ->selectRaw('users.id as user_id')
            ->whereKeyNot($senderId)
            ->where('is_expert', true)
            ->where('expert_subscribes_to_all_topics', true);

        return TopicSubscription::query()
            ->select('user_id')
            ->where('topic_id', $topicId)
            ->where('user_id', '!=', $senderId)
            ->union($expertTopicSubscribers)
            ->union($allTopicExpertSubscribers);
    }

    /**
     * @param  array<int, int>  $subscriberIds
     * @param  array<string, mixed>  $context
     */
    protected function notifyTopicSubscriberChunk(
        array $subscriberIds,
        Topic $topic,
        Message $message,
        User $sender,
        array $context,
    ): void {
        $alreadyDeliveredIds = DB::table('topic_notification_deliveries')
            ->where('message_id', $message->id)
            ->whereIn('user_id', $subscriberIds)
            ->pluck('user_id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        $pendingIds = array_values(array_diff($subscriberIds, $alreadyDeliveredIds));
        if (empty($pendingIds)) {
            return;
        }

        $recipients = User::query()
            ->whereIn('id', $pendingIds)
            ->get()
            ->keyBy('id');
        $firstFailure = null;

        foreach ($pendingIds as $userId) {
            $receiver = $recipients->get($userId);
            if (! ($receiver instanceof User)) {
                continue;
            }

            $perReceiverContext = array_merge($context, [
                'receiver_ids' => [$userId],
            ]);

            try {
                DB::transaction(function () use ($userId, $message, $receiver, $topic, $sender): void {
                    $claimed = DB::table('topic_notification_deliveries')
                        ->insertOrIgnore([
                            'user_id' => $userId,
                            'message_id' => $message->id,
                            'created_at' => now(),
                        ]);

                    if ($claimed !== 1) {
                        return;
                    }

                    Notification::send(
                        $receiver,
                        new TopicReplyNotification($topic, $message, $sender)
                    );
                }, 3);
            } catch (\Throwable $exception) {
                $this->reportNotificationFailure(
                    'Failed to send topic reply notification.',
                    $exception,
                    $perReceiverContext
                );
                $firstFailure ??= $exception;
            }
        }

        if ($firstFailure instanceof \Throwable) {
            throw $firstFailure;
        }
    }

    /**
     * Notify the private-message receiver after a successful send.
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
                ! ($conversation instanceof Conversation) ||
                ! $conversation->isPrivate() ||
                ! ($message instanceof Message) ||
                ! ($sender instanceof User)
            ) {
                return;
            }

            $receiverId = $conversation->direct_user1_id === $senderId
                ? $conversation->direct_user2_id
                : $conversation->direct_user1_id;

            if (! $receiverId) {
                return;
            }

            $context['receiver_ids'] = [$receiverId];

            $receiver = User::find($receiverId);
            if (! ($receiver instanceof User)) {
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
     * @throws ValidationException
     */
    public function ensurePrivateReceiverCanReceive(Conversation $conversation, int $senderId): void
    {
        if (! $conversation->isPrivate()) {
            return;
        }

        if (! Settings::shouldEmailVerify()) {
            return;
        }

        $receiverId = $conversation->direct_user1_id === $senderId
            ? $conversation->direct_user2_id
            : $conversation->direct_user1_id;

        if (! $receiverId) {
            throw ValidationException::withMessages([
                'content' => ['მიმოწერა ამ დროისთვის ხელმისაწვდომი არ არის.'],
            ]);
        }

        $receiver = User::query()
            ->select(['id', 'email_verified_at'])
            ->find($receiverId);

        if (! ($receiver instanceof User) || is_null($receiver->email_verified_at)) {
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

        if (! $directUser1Id || ! $directUser2Id) {
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
