<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Collection;

class AccountDeletionService
{
    // Delete own account
    public function delete(User $user): void
    {
        // Self-delete: we also log the user out and kill the current session.
        $this->deleteInternal($user, true);
    }

    // Delete account initiated by admin tools
    public function deleteByAdmin(User $user): void
    {
        // Admin delete: don't touch the admin's session.
        $this->deleteInternal($user, false);
    }

    protected function deleteInternal(User $user, bool $terminateCurrentSession): void
    {
        $userId = (int) $user->getAuthIdentifier();
        $currentSessionId = $terminateCurrentSession ? Session::getId() : null;

        // Either all DB operations are committed, or none are.
        // We only delete physical files after a successful commit.
        $attachmentTargets = DB::transaction(function () use ($userId, $currentSessionId): Collection {
            // Row lock prevents concurrent child inserts that reference this user
            // (private conversations/messages/participants) while deletion is in progress.
            $lockedUser = User::query()
                ->whereKey($userId)
                ->lockForUpdate()
                ->firstOrFail();

            [$privateConversationIds, $attachmentTargets] = $this->drainPrivateConversations($userId);

            $this->deleteNotificationsByNotifiable($lockedUser);
            $this->deleteNotificationsByPayload($userId, $privateConversationIds);

            $this->deletePasswordResetTokens($lockedUser->email);
            $this->deleteDatabaseSessions($userId, $currentSessionId);

            // Delete user (User Modal deleting hook removes avatar image to)
            $lockedUser->delete();

            return $attachmentTargets;
        });

        $this->deletePhysicalAttachmentFiles($attachmentTargets);

        if ($terminateCurrentSession) {
            // Finally, kick the user out (only for self-delete).
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();
        }
    }

    /**
     * Iterate private conversations in chunks and drain them inside the same transaction.
     *
     * @return array{
     *     0: Collection<int, int>,
     *     1: Collection<int, array{disk:string,path:string}>
     * }
     */
    protected function drainPrivateConversations(int $userId): array
    {
        /** @var array<int, true> $conversationIdSet */
        $conversationIdSet = [];
        /** @var array<string, array{disk:string,path:string}> $attachmentTargetSet */
        $attachmentTargetSet = [];

        $this->privateConversationQuery($userId)
            ->select('id')
            ->chunkById(200, function (Collection $rows) use (&$conversationIdSet, &$attachmentTargetSet): void {
                $chunkIds = $rows
                    ->pluck('id')
                    ->map(fn($id) => (int) $id)
                    ->values();

                if ($chunkIds->isEmpty()) {
                    return;
                }

                foreach ($chunkIds as $id) {
                    $conversationIdSet[$id] = true;
                }

                $targets = $this->collectPrivateConversationAttachmentTargets($chunkIds);
                foreach ($targets as $target) {
                    $attachmentTargetSet[$target['disk'] . '|' . $target['path']] = $target;
                }

                $this->deletePrivateConversations($chunkIds);
            }, 'id');

        return [
            collect(array_keys($conversationIdSet))
                ->map(fn($id) => (int) $id)
                ->values(),
            collect(array_values($attachmentTargetSet))->values(),
        ];
    }

    protected function privateConversationQuery(int $userId): QueryBuilder
    {
        return DB::table('conversations')
            ->where('kind', Conversation::KIND_PRIVATE)
            ->where(function ($query) use ($userId) {
                $query->where('direct_user1_id', $userId)
                    ->orWhere('direct_user2_id', $userId)
                    ->orWhereExists(function ($subquery) use ($userId) {
                        $subquery->selectRaw('1')
                            ->from('conversation_participants as cp')
                            ->whereColumn('cp.conversation_id', 'conversations.id')
                            ->where('cp.user_id', $userId);
                    });
            });
    }

    /**
     * Delete private conversations and rely on cascades for related DB records.
     *
     * @param Collection<int, int> $conversationIds
     */
    protected function deletePrivateConversations(Collection $conversationIds): void
    {
        if ($conversationIds->isEmpty()) {
            return;
        }

        foreach ($conversationIds->chunk(200) as $idChunk) {
            DB::table('conversations')
                ->whereIn('id', $idChunk->all())
                ->delete();
        }
    }

    /**
     * Collect physical file targets for attachments from private conversations.
     * DB rows are deleted by FK cascades when conversations are deleted.
     *
     * @param Collection<int, int> $conversationIds
     * @return Collection<int, array{disk:string,path:string}>
     */
    protected function collectPrivateConversationAttachmentTargets(Collection $conversationIds): Collection
    {
        if ($conversationIds->isEmpty()) {
            return collect();
        }

        $targets = collect();

        DB::table('message_attachments as ma')
            ->join('messages as m', 'm.id', '=', 'ma.message_id')
            ->whereIn('m.conversation_id', $conversationIds->all())
            ->select(['ma.id', 'ma.disk', 'ma.path'])
            ->chunkById(500, function (Collection $attachments) use (&$targets): void {
                foreach ($attachments as $attachment) {
                    $targets->push([
                        'disk' => (string) $attachment->disk,
                        'path' => (string) $attachment->path,
                    ]);
                }
            }, 'ma.id', 'id');

        return $targets;
    }

    /**
     * Delete physical attachment files after DB transaction commit.
     *
     * @param Collection<int, array{disk:string,path:string}> $attachmentTargets
     */
    protected function deletePhysicalAttachmentFiles(Collection $attachmentTargets): void
    {
        if ($attachmentTargets->isEmpty()) {
            return;
        }

        foreach ($attachmentTargets as $attachment) {
            try {
                Storage::disk($attachment['disk'])->delete($attachment['path']);
            } catch (\Throwable $exception) {
                // File cleanup should be best-effort and never rollback committed DB changes.
                report($exception);
            }
        }
    }

    protected function deleteNotificationsByNotifiable(User $user): void
    {
        // Standard Laravel notifications: (type,id) polymorphic columns.
        DB::table('notifications')
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey())
            ->delete();
    }

    protected function deleteNotificationsByPayload(int $userId, Collection $conversationIds): void
    {
        // Some notifications reference users/conversations only inside JSON payload.
        $conversationLookup = $conversationIds->flip();
        $deleteIds = [];

        // Cursor avoids loading the whole notifications table into memory.
        foreach (DB::table('notifications')->select(['id', 'data'])->cursor() as $notification) {
            $payload = json_decode((string) $notification->data, true);

            if (!is_array($payload)) {
                continue;
            }

            $senderId = isset($payload['sender_id']) ? (int) $payload['sender_id'] : null;
            $conversationId = isset($payload['conversation_id']) ? (int) $payload['conversation_id'] : null;

            // Delete if the user is the sender OR the notification points to a deleted private conversation.
            if ($senderId === $userId || ($conversationId && $conversationLookup->has($conversationId))) {
                $deleteIds[] = (string) $notification->id;
            }
        }

        // Batch delete to keep the query size reasonable.
        foreach (array_chunk($deleteIds, 500) as $idChunk) {
            DB::table('notifications')
                ->whereIn('id', $idChunk)
                ->delete();
        }
    }

    protected function deletePasswordResetTokens(?string $email): void
    {
        if (!filled($email)) {
            return;
        }

        // No FK here, so we clean up by email.
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();
    }

    protected function deleteDatabaseSessions(int $userId, ?string $currentSessionId = null): void
    {
        if (config('session.driver') !== 'database') {
            // If sessions are stored in Redis/files, there's nothing to delete here.
            return;
        }

        $table = config('session.table', 'sessions');

        DB::table($table)
            ->where(function ($query) use ($userId, $currentSessionId) {
                // Remove all sessions for that user...
                $query->where('user_id', $userId);

                // ...and also the current session id (useful in edge cases).
                if (filled($currentSessionId)) {
                    $query->orWhere('id', $currentSessionId);
                }
            })
            ->delete();
    }
}
