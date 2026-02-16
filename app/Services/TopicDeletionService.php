<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\MessageAttachment;
use App\Models\Topic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TopicDeletionService
{
    /**
     * Delete topic plus its topic conversation thread data.
     *
     * Removes:
     * - topic conversation(s)
     * - messages / attachments / likes / participants (via FK cascades)
     * - topic notification delivery rows (via message FK cascades)
     * - notifications payload rows that reference this topic_id
     * - physical attachment files (via MessageAttachment model delete listener)
     */
    public function deleteWithThreadData(Topic $topic): void
    {
        $topicId = (int) $topic->getKey();

        DB::transaction(function () use ($topicId): void {
            $lockedTopic = Topic::query()
                ->whereKey($topicId)
                ->lockForUpdate()
                ->first();

            if (!$lockedTopic) {
                return;
            }

            $conversationIds = Conversation::query()
                ->where('kind', Conversation::KIND_TOPIC)
                ->where('topic_id', $topicId)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->values();

            $this->deleteConversationAttachments($conversationIds);

            $this->deleteTopicConversations($conversationIds);
            $this->deleteNotificationsByTopicPayload($topicId);

            $lockedTopic->delete();
        });
    }

    /**
     * @param Collection<int, int> $conversationIds
     */
    protected function deleteTopicConversations(Collection $conversationIds): void
    {
        if ($conversationIds->isEmpty()) {
            return;
        }

        foreach ($conversationIds->chunk(200) as $idChunk) {
            Conversation::query()
                ->whereIn('id', $idChunk->all())
                ->delete();
        }
    }

    /**
     * @param Collection<int, int> $conversationIds
     */
    protected function deleteConversationAttachments(Collection $conversationIds): void
    {
        if ($conversationIds->isEmpty()) {
            return;
        }

        foreach ($conversationIds->chunk(200) as $conversationIdChunk) {
            DB::table('message_attachments as ma')
                ->join('messages as m', 'm.id', '=', 'ma.message_id')
                ->whereIn('m.conversation_id', $conversationIdChunk->all())
                ->select(['ma.id'])
                ->chunkById(500, function (Collection $attachmentRows): void {
                    if ($attachmentRows->isEmpty()) {
                        return;
                    }

                    $attachments = MessageAttachment::query()
                        ->whereIn('id', $attachmentRows->pluck('id')->all())
                        ->get();

                    foreach ($attachments as $attachment) {
                        $attachment->delete();
                    }
                }, 'ma.id', 'id');
        }
    }

    protected function deleteNotificationsByTopicPayload(int $topicId): void
    {
        $deleteIds = [];

        foreach (DB::table('notifications')->select(['id', 'data'])->cursor() as $notification) {
            $payload = json_decode((string) $notification->data, true);

            if (!is_array($payload)) {
                continue;
            }

            $payloadTopicId = isset($payload['topic_id']) ? (int) $payload['topic_id'] : null;
            if ($payloadTopicId === $topicId) {
                $deleteIds[] = (string) $notification->id;
            }
        }

        foreach (array_chunk($deleteIds, 500) as $idChunk) {
            DB::table('notifications')
                ->whereIn('id', $idChunk)
                ->delete();
        }
    }
}
