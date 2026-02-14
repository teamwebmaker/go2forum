<?php

namespace App\Services;

use App\Models\Conversation;
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
     * - physical attachment files (best effort, after commit)
     */
    public function deleteWithThreadData(Topic $topic): void
    {
        $topicId = (int) $topic->getKey();

        $attachmentTargets = DB::transaction(function () use ($topicId): Collection {
            $lockedTopic = Topic::query()
                ->whereKey($topicId)
                ->lockForUpdate()
                ->first();

            if (!$lockedTopic) {
                return collect();
            }

            $conversationIds = Conversation::query()
                ->where('kind', Conversation::KIND_TOPIC)
                ->where('topic_id', $topicId)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->values();

            $attachmentTargets = $this->collectAttachmentTargets($conversationIds);

            $this->deleteTopicConversations($conversationIds);
            $this->deleteNotificationsByTopicPayload($topicId);

            $lockedTopic->delete();

            return $attachmentTargets;
        });

        $this->deletePhysicalAttachmentFiles($attachmentTargets);
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
     * @return Collection<int, array{disk:string,path:string}>
     */
    protected function collectAttachmentTargets(Collection $conversationIds): Collection
    {
        if ($conversationIds->isEmpty()) {
            return collect();
        }

        /** @var array<string, array{disk:string,path:string}> $targetSet */
        $targetSet = [];

        DB::table('message_attachments as ma')
            ->join('messages as m', 'm.id', '=', 'ma.message_id')
            ->whereIn('m.conversation_id', $conversationIds->all())
            ->select(['ma.id', 'ma.disk', 'ma.path'])
            ->chunkById(500, function (Collection $attachments) use (&$targetSet): void {
                foreach ($attachments as $attachment) {
                    $disk = (string) $attachment->disk;
                    $path = (string) $attachment->path;
                    $targetSet[$disk . '|' . $path] = [
                        'disk' => $disk,
                        'path' => $path,
                    ];
                }
            }, 'ma.id', 'id');

        return collect(array_values($targetSet))->values();
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

    /**
     * @param Collection<int, array{disk:string,path:string}> $attachmentTargets
     */
    protected function deletePhysicalAttachmentFiles(Collection $attachmentTargets): void
    {
        if ($attachmentTargets->isEmpty()) {
            return;
        }

        foreach ($attachmentTargets as $attachment) {
            try {
                FileUploadService::deleteUploadedFile(
                    $attachment['path'],
                    null,
                    $attachment['disk']
                );
            } catch (\Throwable $exception) {
                // File cleanup should never rollback already committed DB changes.
                report($exception);
            }
        }
    }
}
