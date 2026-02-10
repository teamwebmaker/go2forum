<?php

namespace App\Services;

use App\Models\User;

class NotificationService
{
    public function markAllRead(User $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }

    public function deleteOne(User $user, string $notificationId): bool
    {
        return $user->notifications()
            ->whereKey($notificationId)
            ->delete() > 0;
    }

    public function clearAll(User $user): void
    {
        $user->notifications()->delete();
    }

    public function clearHistory(User $user, int $keepLatestN = 5): void
    {
        $keepLatestN = max(0, $keepLatestN);

        $keepIds = $user->notifications()
            ->latest()
            ->limit($keepLatestN)
            ->pluck('id');

        if ($keepIds->isEmpty()) {
            $user->notifications()->delete();
            return;
        }

        $user->notifications()
            ->whereNotIn('id', $keepIds)
            ->delete();
    }
}
