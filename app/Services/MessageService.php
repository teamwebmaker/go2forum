<?php

namespace App\Services;

use App\Jobs\SendPrivateMessageNotification;
use App\Jobs\SendTopicReplyNotifications;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageLike;
use App\Models\Topic;
use App\Models\User;
use App\Support\MessagePayloadTransformer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MessageService
{
    protected ?MessageServiceSupport $support = null;
    protected ?MessagePayloadTransformer $payloadTransformer = null;

    /**
     * Create and persist a new message with optional attachments.
     *
     * @param Conversation $conversation
     * @param User $sender
     * @param string|null $content
     * @param array $files
     * @return Message
     * @throws AuthorizationException
     * @throws ValidationException
     */


    public function sendMessage(
        Conversation $conversation,
        User $sender,
        ?string $content,
        array $files = [],
        ?int $replyToMessageId = null,
        ?string $idempotencyKey = null
    ): Message {
        $support = $this->support();
        $senderId = (int) $sender->id;

        $content = $support->normalizeContent($content);
        $files = $support->filterUploadedFiles($files);
        $replyToMessageId = $replyToMessageId && $replyToMessageId > 0
            ? $replyToMessageId
            : null;
        $idempotencyKey = $this->normalizeIdempotencyKey($idempotencyKey);

        if (!$content && empty($files)) {
            throw ValidationException::withMessages([
                'content' => ['შეტყობინება ან მინიმუმ ერთი დანართი სავალდებულოა.'],
            ]);
        }

        $support->authorizeConversationAccess($conversation, $senderId);

        return DB::transaction(function () use ($conversation, $senderId, $content, $files, $support, $replyToMessageId, $idempotencyKey) {
            $lockedConversation = Conversation::query()
                ->whereKey($conversation->id)
                ->lockForUpdate()
                ->firstOrFail();

            $support->authorizeConversationAccess($lockedConversation, $senderId);

            if ($idempotencyKey) {
                $existing = Message::query()
                    ->withTrashed()
                    ->where('conversation_id', $lockedConversation->id)
                    ->where('sender_id', $senderId)
                    ->where('client_token', $idempotencyKey)
                    ->first();

                if ($existing) {
                    return $existing->load([
                        'attachments',
                        'sender',
                        'conversation:id,kind',
                        'replyTo.sender',
                        'replyTo.attachments',
                    ]);
                }
            }

            $replyTarget = $replyToMessageId
                ? $this->resolveReplyTargetWithinTransaction($lockedConversation, $replyToMessageId)
                : null;

            try {
                $message = Message::create([
                    'conversation_id' => $lockedConversation->id,
                    'sender_id' => $senderId,
                    'reply_to_message_id' => $replyTarget?->id,
                    'client_token' => $idempotencyKey,
                    'content' => $content,
                ]);
            } catch (QueryException $exception) {
                if (!$idempotencyKey || !$this->isUniqueConstraintViolation($exception)) {
                    throw $exception;
                }

                $existing = Message::query()
                    ->withTrashed()
                    ->where('conversation_id', $lockedConversation->id)
                    ->where('sender_id', $senderId)
                    ->where('client_token', $idempotencyKey)
                    ->first();

                if ($existing) {
                    return $existing->load([
                        'attachments',
                        'sender',
                        'conversation:id,kind',
                        'replyTo.sender',
                        'replyTo.attachments',
                    ]);
                }

                throw $exception;
            }

            $support->storeAttachments($lockedConversation, $message, $files);
            $support->ensureParticipants($lockedConversation, $senderId);

            if ($lockedConversation->isTopic() && $lockedConversation->topic_id) {
                // Topic-specific bookkeeping.
                $support->ensureSubscription($lockedConversation->topic_id, $senderId);
                Topic::whereKey($lockedConversation->topic_id)->increment('messages_count');
            }

            $lockedConversation->update([
                'last_message_at' => $message->created_at,
            ]);

            $messageId = (int) $message->id;
            $conversationId = (int) $lockedConversation->id;
            $topicId = $lockedConversation->topic_id ? (int) $lockedConversation->topic_id : null;

            // Send notifications.
            // Notify topic subscribers.
            if ($lockedConversation->isTopic() && $lockedConversation->topic_id) {
                // Defer notifications until after the transaction commits.
                DB::afterCommit(function () use ($senderId, $messageId, $conversationId, $topicId): void {
                    try {
                        SendTopicReplyNotifications::dispatch(
                            $senderId,
                            $messageId,
                            (int) $topicId
                        )->afterResponse();
                    } catch (\Throwable $exception) {
                        logger()->error('Failed to execute topic notification callback.', [
                            'message_id' => $messageId,
                            'conversation_id' => $conversationId,
                            'topic_id' => $topicId,
                            'sender_id' => $senderId,
                            'exception_class' => $exception::class,
                            'exception_message' => $exception->getMessage(),
                        ]);
                        report($exception);
                    }
                });
            }

            // Notify private receiver.
            if ($lockedConversation->isPrivate()) {
                DB::afterCommit(function () use ($senderId, $messageId, $conversationId): void {
                    try {
                        SendPrivateMessageNotification::dispatch(
                            $senderId,
                            $messageId,
                            $conversationId
                        )->afterResponse();
                    } catch (\Throwable $exception) {
                        logger()->error('Failed to execute private notification callback.', [
                            'message_id' => $messageId,
                            'conversation_id' => $conversationId,
                            'sender_id' => $senderId,
                            'exception_class' => $exception::class,
                            'exception_message' => $exception->getMessage(),
                        ]);
                        report($exception);
                    }
                });
            }

            return $message->load([
                'attachments',
                'sender',
                'conversation:id,kind',
                'replyTo.sender',
                'replyTo.attachments',
            ]);
        });
    }

    /**
     * List messages using cursor pagination and enrich payload metadata.
     *
     * @param Conversation $conversation
     * @param Carbon|null $cursorCreatedAt
     * @param int|null $cursorId
     * @param int $limit
     * @param int|null $currentUserId
     * @return array
     */
    public function listMessages(
        Conversation $conversation,
        ?Carbon $cursorCreatedAt,
        ?int $cursorId,
        int $limit,
        ?int $currentUserId
    ): array {
        $support = $this->support();
        $support->authorizeConversationRead($conversation, $currentUserId);

        $query = Message::query()
            ->withTrashed()
            ->where('conversation_id', $conversation->id);

        // Cursor pagination: older than (created_at, id) tuple.
        if ($cursorCreatedAt && $cursorId) {
            $query->where(function ($q) use ($cursorCreatedAt, $cursorId) {
                $q->where('created_at', '<', $cursorCreatedAt)
                    ->orWhere(function ($q) use ($cursorCreatedAt, $cursorId) {
                        $q->where('created_at', $cursorCreatedAt)
                            ->where('id', '<', $cursorId);
                    });
            });
        }

        $messages = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->with([
                'sender:id,name,surname,nickname,image,is_expert,is_top_commentator',
                'attachments',
                'conversation:id,kind',
                'replyTo:id,conversation_id,sender_id,content,deleted_at',
                'replyTo.sender:id,name,surname,nickname,image,is_expert,is_top_commentator',
                'replyTo.attachments:id,message_id',
            ])
            ->get();

        $messageIds = $messages->pluck('id');
        [$likeCounts, $likedByMe] = $support->loadMessageLikes($messageIds, $currentUserId);
        $transformer = $this->payloadTransformer();

        $payload = $messages->map(function (Message $message) use ($currentUserId, $likeCounts, $likedByMe, $transformer) {
            return $transformer->transform(
                $message,
                $currentUserId,
                (int) ($likeCounts[$message->id] ?? 0),
                $currentUserId ? $likedByMe->has($message->id) : false
            );
        })->values();

        return ['messages' => $payload];
    }

    /**
     * Edit a message content.
     * - Only author can edit
     * - Editing is allowed only within the configured edit window (1 day)
     * - Preserves first/original content and latest edited version in dedicated columns
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function editMessage(
        Message $message,
        int $currentUserId,
        ?string $newContent,
        bool $topicOnly = false
    ): Message {
        $support = $this->support();
        $message->loadMissing(['conversation', 'attachments', 'sender']);

        if ((int) ($message->sender_id ?? 0) !== $currentUserId) {
            throw new AuthorizationException('You can only edit your own messages.');
        }

        $conversation = $message->conversation;
        if (!$conversation) {
            throw new AuthorizationException('Conversation not found for this message.');
        }

        if ($topicOnly && !$conversation->isTopic()) {
            throw new AuthorizationException('Editing is available only for topic messages.');
        }

        $support->authorizeConversationRead($conversation, $currentUserId);

        if (!$message->isEditableBy($currentUserId)) {
            throw ValidationException::withMessages([
                'content' => ['მესიჯის ჩასწორება შესაძლებელია მხოლოდ 24 საათის განმავლობაში.'],
            ]);
        }

        $normalized = $support->normalizeContent($newContent);
        if (!$normalized) {
            throw ValidationException::withMessages([
                'content' => ['შესანახად მიუთითეთ ტექსტი.'],
            ]);
        }

        if ($normalized === (string) ($message->content ?? '')) {
            return $message->load(['attachments', 'sender', 'conversation:id,kind']);
        }

        return DB::transaction(function () use ($message, $normalized) {
            $message->forceFill([
                'original_content' => $message->original_content ?? $message->content,
                'edited_content' => $normalized,
                'content' => $normalized,
                'edited_at' => now(),
            ])->save();

            return $message->fresh(['attachments', 'sender', 'conversation:id,kind']);
        });
    }

    /**
     * Like a message and return the updated like count.
     *
     * @param Message $message
     * @param int $userId
     * @return int
     */
    public function likeMessage(Message $message, int $userId): int
    {
        $this->support()->authorizeMessageReaction($message, $userId);

        MessageLike::query()->insertOrIgnore([
            'message_id' => $message->id,
            'user_id' => $userId,
            'created_at' => now(),
        ]);

        return MessageLike::where('message_id', $message->id)->count();
    }

    /**
     * Unlike a message and return the updated like count.
     *
     * @param Message $message
     * @param int $userId
     * @return int
     */
    public function unlikeMessage(Message $message, int $userId): int
    {
        $this->support()->authorizeMessageReaction($message, $userId);

        MessageLike::where('message_id', $message->id)
            ->where('user_id', $userId)
            ->delete();

        return MessageLike::where('message_id', $message->id)->count();
    }

    /**
     * Soft delete a message and optionally remove its attachments.
     *
     * @param Message $message
     * @param int $currentUserId
     * @param bool $isAdmin
     * @return void
     * @throws AuthorizationException
     */
    public function deleteMessage(Message $message, int $currentUserId, bool $isAdmin = false): void
    {
        $message->loadMissing('conversation');

        if (!$isAdmin) {
            if ($message->sender_id !== $currentUserId) {
                throw new AuthorizationException('You can only delete your own messages.');
            }

            $this->support()->authorizeConversationRead($message->conversation, $currentUserId);
        }

        DB::transaction(function () use ($message) {
            $message->loadMissing(['conversation', 'attachments']);
            $alreadyDeleted = $message->trashed();

            $message->delete();

            if (!$alreadyDeleted && $message->conversation?->isTopic() && $message->conversation->topic_id) {
                Topic::whereKey($message->conversation->topic_id)->decrement('messages_count');
            }

            if (config('chat.delete_attachments_on_message_delete', false)) {
                foreach ($message->attachments as $attachment) {
                    Storage::disk($attachment->disk)->delete($attachment->path);
                }

                $message->attachments()->delete();
            }
        });
    }

    /**
     * Resolve the helper instance used by this service.
     *
     * @return MessageServiceSupport
     */
    protected function support(): MessageServiceSupport
    {
        if (!$this->support) {
            $this->support = new MessageServiceSupport();
        }

        return $this->support;
    }

    protected function payloadTransformer(): MessagePayloadTransformer
    {
        if (!$this->payloadTransformer) {
            $this->payloadTransformer = app(MessagePayloadTransformer::class);
        }

        return $this->payloadTransformer;
    }

    protected function resolveReplyTargetWithinTransaction(Conversation $conversation, int $replyToMessageId): Message
    {
        $replyTarget = Message::withTrashed()
            ->whereKey($replyToMessageId)
            ->lockForUpdate()
            ->first();

        if (
            !$replyTarget ||
            (int) $replyTarget->conversation_id !== (int) $conversation->id ||
            $replyTarget->trashed()
        ) {
            throw ValidationException::withMessages([
                'reply_to_message_id' => ['არჩეული მესიჯი პასუხისთვის ვერ მოიძებნა.'],
            ]);
        }

        return $replyTarget;
    }

    protected function normalizeIdempotencyKey(?string $idempotencyKey): ?string
    {
        if (!is_string($idempotencyKey)) {
            return null;
        }

        $idempotencyKey = trim($idempotencyKey);
        if ($idempotencyKey === '') {
            return null;
        }

        if (strlen($idempotencyKey) > 64) {
            return substr($idempotencyKey, 0, 64);
        }

        return $idempotencyKey;
    }

    protected function isUniqueConstraintViolation(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;
        $driverCode = (string) ($exception->errorInfo[1] ?? '');
        $message = $exception->getMessage();

        return $sqlState === '23000'
            || $sqlState === '23505'
            || in_array($driverCode, ['1062', '19', '1555', '2067'], true)
            || str_contains(strtolower($message), 'unique');
    }
}
