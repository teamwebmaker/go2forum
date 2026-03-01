<?php

namespace App\Livewire;

use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationsDropdown extends Component
{
    // private const LIST_LIMIT = 10;
    private const PRUNE_THRESHOLD = 20;
    private const PRUNE_KEEP = 5;

    public bool $open = false;
    /** @var array<int, array<string, mixed>> */
    public array $notificationItems = [];
    public int $unreadCount = 0;
    public int $totalCount = 0;

    public function mount(): void
    {
        $this->reloadState();
    }

    public function togglePanel(): void
    {
        $this->open = !$this->open;

        if ($this->open) {
            $this->reloadState();
        }
    }

    public function closePanel(): void
    {
        $this->open = false;
    }

    public function markAllRead(NotificationService $notificationService): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $notificationService->markAllRead($user);
        $this->reloadState();
    }

    public function clearAll(NotificationService $notificationService): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $notificationService->clearAll($user);
        $this->reloadState();
    }

    public function clearHistory(NotificationService $notificationService): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $notificationService->clearHistory($user, self::PRUNE_KEEP);
        $this->reloadState();
    }

    public function deleteNotification(string $notificationId, NotificationService $notificationService): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $notificationService->deleteOne($user, $notificationId);
        $this->reloadState();
    }

    public function render()
    {
        return view('livewire.notifications-dropdown', [
            'notificationItems' => $this->notificationItems,
            'unreadCount' => $this->unreadCount,
            'totalCount' => $this->totalCount,
            'pruneThreshold' => self::PRUNE_THRESHOLD,
            'pruneKeep' => self::PRUNE_KEEP,
        ]);
    }

    protected function reloadState(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->notificationItems = [];
            $this->unreadCount = 0;
            $this->totalCount = 0;
            return;
        }

        $counts = $user->notifications()
            ->reorder()
            ->toBase()
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN read_at IS NULL THEN 1 ELSE 0 END), 0) as unread_count')
            ->first();

        $this->totalCount = (int) ($counts->total_count ?? 0);
        $this->unreadCount = (int) ($counts->unread_count ?? 0);

        $this->notificationItems = $user->notifications()
            ->latest()
            // ->limit(self::LIST_LIMIT)
            ->get()
            ->map(function ($notification): array {
                return [
                    'id' => $notification->id,
                    'data' => is_array($notification->data) ? $notification->data : [],
                    'is_unread' => is_null($notification->read_at),
                    'created_at_human' => $notification->created_at?->diffForHumans(),
                ];
            })
            ->all();
    }
}
