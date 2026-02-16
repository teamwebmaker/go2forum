<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ConversationDeletionService
{
    /**
     * Delete a conversation with related cleanup.
     *
     * Removes:
     * - conversation row (DB cascades remove messages, likes, attachments, participants)
     * - notification rows that reference the conversation_id in JSON payload
     * - physical attachment files (best effort, after commit)
     */
    public function deleteByAdmin(Conversation $conversation): void
    {
        $conversationId = (int) $conversation->getKey();

        $attachmentTargets = DB::transaction(function () use ($conversationId): Collection {
            $lockedConversation = Conversation::query()
                ->whereKey($conversationId)
                ->lockForUpdate()
                ->first();

            if (!$lockedConversation) {
                return collect();
            }

            $attachmentTargets = $this->collectAttachmentTargets($conversationId);

            $this->deleteNotificationsByConversationPayload($conversationId);

            $lockedConversation->delete();

            return $attachmentTargets;
        });

        $this->deletePhysicalAttachmentFiles($attachmentTargets);
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

    protected function deleteNotificationsByConversationPayload(int $conversationId): void
    {
        $deleteIds = [];

        foreach (DB::table('notifications')->select(['id', 'data'])->cursor() as $notification) {
            $payload = json_decode((string) $notification->data, true);

            if (!is_array($payload)) {
                continue;
            }

            $payloadConversationId = isset($payload['conversation_id']) ? (int) $payload['conversation_id'] : null;
            if ($payloadConversationId === $conversationId) {
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
