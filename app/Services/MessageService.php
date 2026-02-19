<?php

namespace App\Services;

use App\Jobs\SendPrivateMessageNotification;
use App\Jobs\SendTopicReplyNotifications;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageLike;
use App\Models\Topic;
use App\Support\MessagePayloadTransformer;
use Illuminate\Auth\Access\AuthorizationException;
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
     * @param int $senderId
     * @param string|null $content
     * @param array $files
     * @return Message
     * @throws AuthorizationException
     * @throws ValidationException
     */


    public function sendMessage(
        Conversation $conversation,
        int $senderId,
        ?string $content,
        array $files = []
    ): Message {
        $support = $this->support();

        $content = $support->normalizeContent($content);
        $files = $support->filterUploadedFiles($files);

        if (!$content && empty($files)) {
            throw ValidationException::withMessages([
                'content' => ['შეტყობინება ან მინიმუმ ერთი დანართი სავალდებულოა.'],
            ]);
        }

        $support->authorizeConversationAccess($conversation, $senderId);

        return DB::transaction(function () use ($conversation, $senderId, $content, $files, $support) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $senderId,
                'content' => $content,
            ]);

            $support->storeAttachments($conversation, $message, $files);
            $support->ensureParticipants($conversation, $senderId);

            if ($conversation->isTopic() && $conversation->topic_id) {
                // Topic-specific bookkeeping.
                $support->ensureSubscription($conversation->topic_id, $senderId);
                Topic::whereKey($conversation->topic_id)->increment('messages_count');
            }

            $conversation->update([
                'last_message_at' => $message->created_at,
            ]);

            $messageId = (int) $message->id;
            $conversationId = (int) $conversation->id;
            $topicId = $conversation->topic_id ? (int) $conversation->topic_id : null;

            // Send notifications.
            // Notify topic subscribers.
            if ($conversation->isTopic() && $conversation->topic_id) {
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
            if ($conversation->isPrivate()) {
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

            return $message->load(['attachments', 'sender', 'conversation:id,kind']);
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
            ->with(['sender:id,name,surname,image,is_expert,is_top_commentator', 'attachments', 'conversation:id,kind'])
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
            $this->payloadTransformer = new MessagePayloadTransformer();
        }

        return $this->payloadTransformer;
    }
}
