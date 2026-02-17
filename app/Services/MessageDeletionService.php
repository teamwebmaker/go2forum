<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Topic;
use Illuminate\Support\Facades\DB;

class MessageDeletionService
{
    /**
     * Permanently delete a message from admin tools.
     *
     * Removes:
     * - message row (hard delete)
     * - message attachments (model delete events remove physical files)
     * - message likes and topic notification deliveries (via FK cascades)
     */
    public function deleteByAdmin(Message $message): void
    {
        $messageId = (int) $message->getKey();

        DB::transaction(function () use ($messageId): void {
            $lockedMessage = Message::query()
                ->withTrashed()
                ->with('conversation:id,kind,topic_id')
                ->whereKey($messageId)
                ->lockForUpdate()
                ->first();

            if (!$lockedMessage) {
                return;
            }

            $wasSoftDeleted = $lockedMessage->trashed();
            $topicId = $lockedMessage->conversation?->isTopic()
                ? (int) ($lockedMessage->conversation->topic_id ?? 0)
                : 0;

            $lockedMessage->attachments()->each(function ($attachment): void {
                $attachment->delete();
            });

            $lockedMessage->forceDelete();

            if (!$wasSoftDeleted && $topicId > 0) {
                Topic::query()
                    ->whereKey($topicId)
                    ->where('messages_count', '>', 0)
                    ->decrement('messages_count');
            }
        });
    }
}
