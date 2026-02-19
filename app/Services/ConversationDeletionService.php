<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Topic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConversationDeletionService
{
    /**
     * Delete a conversation with related cleanup.
     *
     * Removes:
     * - conversation row (DB cascades remove messages, likes, attachments, participants)
     * - notification rows that reference conversation_id/topic_id in JSON payload
     * - physical attachment files (best effort, after commit)
     * - physical conversation attachment directory (best effort, after commit)
     */
    public function deleteByAdmin(Conversation $conversation): void
    {
        $conversationId = (int) $conversation->getKey();

        [$attachmentTargets, $notificationContext] = DB::transaction(function () use ($conversationId): array {
            $lockedConversation = Conversation::query()
                ->whereKey($conversationId)
                ->lockForUpdate()
                ->first();

            if (!$lockedConversation) {
                return [collect(), null];
            }

            $attachmentTargets = $this->collectAttachmentTargets($conversationId);
            $notificationContext = [
                'conversation_id' => $conversationId,
                'topic_id' => $lockedConversation->topic_id ? (int) $lockedConversation->topic_id : null,
            ];

            $lockedConversation->delete();

            if ($lockedConversation->isTopic() && $lockedConversation->topic_id) {
                $this->recalculateTopicMessagesCount((int) $lockedConversation->topic_id);
            }

            return [$attachmentTargets, $notificationContext];
        });

        if (is_array($notificationContext)) {
            try {
                $this->deleteRelatedNotifications(
                    conversationId: (int) $notificationContext['conversation_id'],
                    topicId: isset($notificationContext['topic_id']) ? (int) $notificationContext['topic_id'] : null,
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        $this->deletePhysicalAttachmentFiles($attachmentTargets);

        if (is_array($notificationContext)) {
            $disks = $attachmentTargets
                ->pluck('disk')
                ->map(fn($disk): string => (string) $disk)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $this->deleteConversationDirectories(collect([(int) $notificationContext['conversation_id']]), $disks);
        }
    }

    /**
     * @return Collection<int, array{disk:string,path:string}>
     */
    protected function collectAttachmentTargets(int $conversationId): Collection
    {
        /** @var array<string, array{disk:string,path:string}> $targetSet */
        $targetSet = [];

        DB::table('message_attachments as ma')
            ->join('messages as m', 'm.id', '=', 'ma.message_id')
            ->where('m.conversation_id', $conversationId)
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

    protected function deleteRelatedNotifications(int $conversationId, ?int $topicId = null): void
    {
        $conversationId = max(0, $conversationId);
        $topicId = $topicId && $topicId > 0 ? $topicId : null;

        if ($conversationId === 0 && $topicId === null) {
            return;
        }

        try {
            DB::table('notifications')
                ->where(function ($query) use ($conversationId, $topicId): void {
                    if ($conversationId > 0) {
                        $query->whereRaw(
                            "JSON_VALID(data) AND CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.conversation_id')) AS UNSIGNED) = ?",
                            [$conversationId]
                        );
                    }

                    if ($topicId !== null) {
                        $query->orWhereRaw(
                            "JSON_VALID(data) AND CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.topic_id')) AS UNSIGNED) = ?",
                            [$topicId]
                        );
                    }
                })
                ->delete();

            return;
        } catch (\Throwable $exception) {
            // Fallback for DB engines/configurations without JSON_* support on text payload.
            report($exception);
        }

        $deleteIds = [];

        foreach (DB::table('notifications')->select(['id', 'data'])->cursor() as $notification) {
            $payload = json_decode((string) $notification->data, true);

            if (!is_array($payload)) {
                continue;
            }

            $payloadConversationId = isset($payload['conversation_id']) ? (int) $payload['conversation_id'] : null;
            $payloadTopicId = isset($payload['topic_id']) ? (int) $payload['topic_id'] : null;

            if (
                ($conversationId > 0 && $payloadConversationId === $conversationId)
                || ($topicId !== null && $payloadTopicId === $topicId)
            ) {
                $deleteIds[] = (string) $notification->id;
            }
        }

        foreach (array_chunk($deleteIds, 500) as $idChunk) {
            DB::table('notifications')
                ->whereIn('id', $idChunk)
                ->delete();
        }
    }

    protected function recalculateTopicMessagesCount(int $topicId): void
    {
        $messagesCount = (int) DB::table('messages as m')
            ->join('conversations as c', 'c.id', '=', 'm.conversation_id')
            ->where('c.kind', Conversation::KIND_TOPIC)
            ->where('c.topic_id', $topicId)
            ->whereNull('m.deleted_at')
            ->count();

        Topic::query()
            ->whereKey($topicId)
            ->update(['messages_count' => $messagesCount]);
    }

    /**
     * @param Collection<int, int> $conversationIds
     * @param array<int, string> $disks
     */
    protected function deleteConversationDirectories(Collection $conversationIds, array $disks = []): void
    {
        if ($conversationIds->isEmpty()) {
            return;
        }

        $defaultDisk = (string) config('chat.attachments_disk', 'public');
        $allDisks = collect(array_merge([$defaultDisk], $disks))
            ->map(fn($disk): string => (string) $disk)
            ->filter()
            ->unique()
            ->values();

        foreach ($conversationIds as $conversationId) {
            $conversationId = (int) $conversationId;
            if ($conversationId <= 0) {
                continue;
            }

            $directory = Conversation::ATTACHMENT_DIR_PREFIX . $conversationId;

            foreach ($allDisks as $disk) {
                try {
                    Storage::disk((string) $disk)->deleteDirectory($directory);
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }
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
