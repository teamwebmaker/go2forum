<?php

namespace App\Filament\Pages;

use App\Models\Message;
use App\Models\Topic;
use App\Models\User;
use App\Services\AccountDeletionService;
use App\Services\MessageDeletionService;
use App\Services\TopicDeletionService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Trash extends Page
{
    use WithPagination;

    protected const PER_PAGE = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBoxXMark;
    protected static ?int $navigationSort = 9;

    protected string $view = 'filament.pages.trash';

    #[Url(as: 'tab')]
    public string $activeTab = 'users';

    #[Url(as: 'users_q')]
    public string $usersSearch = '';

    #[Url(as: 'topics_q')]
    public string $topicsSearch = '';

    #[Url(as: 'messages_q')]
    public string $messagesSearch = '';

    public bool $selectAllUsers = false;
    public bool $selectAllTopics = false;
    public bool $selectAllMessages = false;

    public array $selectedUsers = [];
    public array $selectedTopics = [];
    public array $selectedMessages = [];

    public bool $showDetailsModal = false;
    public string $detailsTitle = '';
    public array $detailsRows = [];

    public function getTitle(): string|Htmlable
    {
        return __('models.trash.titles.global');
    }

    public static function getNavigationLabel(): string
    {
        return __('models.trash.navigation');
    }

    public function setTab(string $tab): void
    {
        if (!in_array($tab, ['users', 'topics', 'messages'], true)) {
            return;
        }

        $this->activeTab = $tab;
        $this->resetPage('usersPage');
        $this->resetPage('topicsPage');
        $this->resetPage('messagesPage');
        $this->clearSelections();
    }

    public function mount(): void
    {
        if (!in_array($this->activeTab, ['users', 'topics', 'messages'], true)) {
            $this->activeTab = 'users';
        }
    }

    public function getDeletedUsersProperty()
    {
        $query = User::onlyTrashed();

        $this->applyDeletedUsersSearch($query);

        return $query
            ->orderByDesc('deleted_at')
            ->paginate(self::PER_PAGE, ['*'], 'usersPage');
    }

    public function getDeletedTopicsProperty()
    {
        $query = Topic::onlyTrashed()
            ->with(['category:id,name', 'user:id,name,surname,nickname']);

        $this->applyDeletedTopicsSearch($query);

        return $query
            ->orderByDesc('deleted_at')
            ->paginate(self::PER_PAGE, ['*'], 'topicsPage');
    }

    public function getDeletedMessagesProperty()
    {
        $query = Message::query()
            ->withTrashed()
            ->onlyInTrash()
            ->with(['sender:id,name,surname,nickname']);

        $this->applyDeletedMessagesSearch($query);

        return $query
            ->orderByDesc('trashed_at')
            ->paginate(self::PER_PAGE, ['*'], 'messagesPage');
    }

    public function restoreUser(int $userId): void
    {
        $user = User::onlyTrashed()->find($userId);
        if (!$user) {
            return;
        }

        try {
            $user->restore();
        } catch (\Throwable $exception) {
            report($exception);
            $this->notifyWarning('ოპერაცია ვერ შესრულდა.');
            return;
        }

        $this->notifySuccess(__('models.trash.notifications.restored'));
    }

    public function forceDeleteUserKeepPublic(int $userId, AccountDeletionService $accountDeletionService): void
    {
        $user = User::withTrashed()->find($userId);
        if (!$user || !$user->trashed()) {
            $this->notifyWarning('საბოლოოდ წაშლა შესაძლებელია მხოლოდ სანაგვეში მყოფ ჩანაწერზე.');
            return;
        }

        try {
            $accountDeletionService->deleteByAdmin($user);
        } catch (\Throwable $exception) {
            report($exception);
            $this->notifyWarning('ოპერაცია ვერ შესრულდა.');
            return;
        }

        $this->notifySuccess(__('models.trash.notifications.force_deleted'));
    }

    public function forceDeleteUserWithPublic(int $userId, AccountDeletionService $accountDeletionService): void
    {
        $user = User::withTrashed()->find($userId);
        if (!$user || !$user->trashed()) {
            $this->notifyWarning('საბოლოოდ წაშლა შესაძლებელია მხოლოდ სანაგვეში მყოფ ჩანაწერზე.');
            return;
        }

        try {
            $accountDeletionService->deleteByAdminWithPublicData($user);
        } catch (\Throwable $exception) {
            report($exception);
            $this->notifyWarning('ოპერაცია ვერ შესრულდა.');
            return;
        }

        $this->notifySuccess(__('models.trash.notifications.force_deleted'));
    }

    public function restoreTopic(int $topicId): void
    {
        $topic = Topic::onlyTrashed()->find($topicId);
        if (!$topic) {
            return;
        }

        try {
            $topic->restore();
        } catch (\Throwable $exception) {
            report($exception);
            $this->notifyWarning('ოპერაცია ვერ შესრულდა.');
            return;
        }

        $this->notifySuccess(__('models.trash.notifications.restored'));
    }

    public function forceDeleteTopicOnly(int $topicId, TopicDeletionService $topicDeletionService): void
    {
        $topic = Topic::withTrashed()->find($topicId);
        if (!$topic || !$topic->trashed()) {
            $this->notifyWarning('საბოლოოდ წაშლა შესაძლებელია მხოლოდ სანაგვეში მყოფ ჩანაწერზე.');
            return;
        }

        try {
            $topicDeletionService->deleteTopicOnly($topic);
        } catch (\Throwable $exception) {
            report($exception);
            $this->notifyWarning('ოპერაცია ვერ შესრულდა.');
            return;
        }

        $this->notifySuccess(__('models.trash.notifications.force_deleted'));
    }

    public function forceDeleteTopicWithThread(int $topicId, TopicDeletionService $topicDeletionService): void
    {
        $topic = Topic::withTrashed()->find($topicId);
        if (!$topic || !$topic->trashed()) {
            $this->notifyWarning('საბოლოოდ წაშლა შესაძლებელია მხოლოდ სანაგვეში მყოფ ჩანაწერზე.');
            return;
        }

        try {
            $topicDeletionService->deleteWithThreadData($topic);
        } catch (\Throwable $exception) {
            report($exception);
            $this->notifyWarning('ოპერაცია ვერ შესრულდა.');
            return;
        }

        $this->notifySuccess(__('models.trash.notifications.force_deleted'));
    }

    public function restoreMessage(int $messageId): void
    {
        $message = Message::query()
            ->withTrashed()
            ->onlyInTrash()
            ->find($messageId);

        if (!$message) {
            return;
        }

        try {
            $message->restoreFromTrash();
        } catch (\Throwable $exception) {
            report($exception);
            $this->notifyWarning('ოპერაცია ვერ შესრულდა.');
            return;
        }

        $this->notifySuccess(__('models.trash.notifications.restored'));
    }

    public function forceDeleteMessage(int $messageId, MessageDeletionService $messageDeletionService): void
    {
        $message = Message::query()
            ->withTrashed()
            ->find($messageId);

        if (!$message || !$message->is_trashed) {
            $this->notifyWarning('საბოლოოდ წაშლა შესაძლებელია მხოლოდ სანაგვეში მყოფ ჩანაწერზე.');
            return;
        }

        try {
            $messageDeletionService->deleteByAdmin($message);
        } catch (\Throwable $exception) {
            report($exception);
            $this->notifyWarning('ოპერაცია ვერ შესრულდა.');
            return;
        }

        $this->notifySuccess(__('models.trash.notifications.force_deleted'));
    }

    public function updatedSelectAllUsers(bool $value): void
    {
        $this->selectedUsers = $value ? $this->currentPageUserIds() : [];
    }

    public function updatedSelectAllTopics(bool $value): void
    {
        $this->selectedTopics = $value ? $this->currentPageTopicIds() : [];
    }

    public function updatedSelectAllMessages(bool $value): void
    {
        $this->selectedMessages = $value ? $this->currentPageMessageIds() : [];
    }

    public function updatedSelectedUsers(): void
    {
        $this->selectAllUsers = $this->hasFullSelection($this->selectedUsers, $this->currentPageUserIds());
    }

    public function updatedSelectedTopics(): void
    {
        $this->selectAllTopics = $this->hasFullSelection($this->selectedTopics, $this->currentPageTopicIds());
    }

    public function updatedSelectedMessages(): void
    {
        $this->selectAllMessages = $this->hasFullSelection($this->selectedMessages, $this->currentPageMessageIds());
    }

    public function updatedUsersSearch(): void
    {
        $this->resetPage('usersPage');
        $this->clearUserSelection();
    }

    public function updatedTopicsSearch(): void
    {
        $this->resetPage('topicsPage');
        $this->clearTopicSelection();
    }

    public function updatedMessagesSearch(): void
    {
        $this->resetPage('messagesPage');
        $this->clearMessageSelection();
    }

    public function restoreSelectedUsers(): void
    {
        $ids = $this->normalizeIds($this->selectedUsers);

        if ($ids === []) {
            return;
        }

        $processed = 0;
        $failed = 0;

        User::onlyTrashed()
            ->whereKey($ids)
            ->get()
            ->each(function (User $user) use (&$processed, &$failed): void {
                try {
                    $user->restore();
                    $processed++;
                } catch (\Throwable $exception) {
                    report($exception);
                    $failed++;
                }
            });

        $this->clearUserSelection();
        $this->notifyBulkOutcome($processed, $failed, 0, __('models.trash.notifications.restored'));
    }

    public function forceDeleteSelectedUsersKeepPublic(AccountDeletionService $accountDeletionService): void
    {
        $ids = $this->normalizeIds($this->selectedUsers);

        if ($ids === []) {
            return;
        }

        $processed = 0;
        $failed = 0;
        $skipped = 0;

        User::withTrashed()
            ->whereKey($ids)
            ->get()
            ->each(function (User $user) use ($accountDeletionService, &$processed, &$failed, &$skipped): void {
                if (!$user->trashed()) {
                    $skipped++;
                    return;
                }

                try {
                    $accountDeletionService->deleteByAdmin($user);
                    $processed++;
                } catch (\Throwable $exception) {
                    report($exception);
                    $failed++;
                }
            });

        $this->clearUserSelection();
        $this->notifyBulkOutcome($processed, $failed, $skipped, __('models.trash.notifications.force_deleted'));
    }

    public function forceDeleteSelectedUsersWithPublic(AccountDeletionService $accountDeletionService): void
    {
        $ids = $this->normalizeIds($this->selectedUsers);

        if ($ids === []) {
            return;
        }

        $processed = 0;
        $failed = 0;
        $skipped = 0;

        User::withTrashed()
            ->whereKey($ids)
            ->get()
            ->each(function (User $user) use ($accountDeletionService, &$processed, &$failed, &$skipped): void {
                if (!$user->trashed()) {
                    $skipped++;
                    return;
                }

                try {
                    $accountDeletionService->deleteByAdminWithPublicData($user);
                    $processed++;
                } catch (\Throwable $exception) {
                    report($exception);
                    $failed++;
                }
            });

        $this->clearUserSelection();
        $this->notifyBulkOutcome($processed, $failed, $skipped, __('models.trash.notifications.force_deleted'));
    }

    public function restoreSelectedTopics(): void
    {
        $ids = $this->normalizeIds($this->selectedTopics);

        if ($ids === []) {
            return;
        }

        $processed = 0;
        $failed = 0;

        Topic::onlyTrashed()
            ->whereKey($ids)
            ->get()
            ->each(function (Topic $topic) use (&$processed, &$failed): void {
                try {
                    $topic->restore();
                    $processed++;
                } catch (\Throwable $exception) {
                    report($exception);
                    $failed++;
                }
            });

        $this->clearTopicSelection();
        $this->notifyBulkOutcome($processed, $failed, 0, __('models.trash.notifications.restored'));
    }

    public function forceDeleteSelectedTopicsOnly(TopicDeletionService $topicDeletionService): void
    {
        $ids = $this->normalizeIds($this->selectedTopics);

        if ($ids === []) {
            return;
        }

        $processed = 0;
        $failed = 0;
        $skipped = 0;

        Topic::withTrashed()
            ->whereKey($ids)
            ->get()
            ->each(function (Topic $topic) use ($topicDeletionService, &$processed, &$failed, &$skipped): void {
                if (!$topic->trashed()) {
                    $skipped++;
                    return;
                }

                try {
                    $topicDeletionService->deleteTopicOnly($topic);
                    $processed++;
                } catch (\Throwable $exception) {
                    report($exception);
                    $failed++;
                }
            });

        $this->clearTopicSelection();
        $this->notifyBulkOutcome($processed, $failed, $skipped, __('models.trash.notifications.force_deleted'));
    }

    public function forceDeleteSelectedTopicsWithThread(TopicDeletionService $topicDeletionService): void
    {
        $ids = $this->normalizeIds($this->selectedTopics);

        if ($ids === []) {
            return;
        }

        $processed = 0;
        $failed = 0;
        $skipped = 0;

        Topic::withTrashed()
            ->whereKey($ids)
            ->get()
            ->each(function (Topic $topic) use ($topicDeletionService, &$processed, &$failed, &$skipped): void {
                if (!$topic->trashed()) {
                    $skipped++;
                    return;
                }

                try {
                    $topicDeletionService->deleteWithThreadData($topic);
                    $processed++;
                } catch (\Throwable $exception) {
                    report($exception);
                    $failed++;
                }
            });

        $this->clearTopicSelection();
        $this->notifyBulkOutcome($processed, $failed, $skipped, __('models.trash.notifications.force_deleted'));
    }

    public function restoreSelectedMessages(): void
    {
        $ids = $this->normalizeIds($this->selectedMessages);

        if ($ids === []) {
            return;
        }

        $processed = 0;
        $failed = 0;

        Message::query()
            ->withTrashed()
            ->whereIn('id', $ids)
            ->where('is_trashed', true)
            ->get()
            ->each(function (Message $message) use (&$processed, &$failed): void {
                try {
                    $message->restoreFromTrash();
                    $processed++;
                } catch (\Throwable $exception) {
                    report($exception);
                    $failed++;
                }
            });

        $this->clearMessageSelection();
        $this->notifyBulkOutcome($processed, $failed, 0, __('models.trash.notifications.restored'));
    }

    public function forceDeleteSelectedMessages(MessageDeletionService $messageDeletionService): void
    {
        $ids = $this->normalizeIds($this->selectedMessages);

        if ($ids === []) {
            return;
        }

        $processed = 0;
        $failed = 0;

        Message::query()
            ->withTrashed()
            ->whereIn('id', $ids)
            ->where('is_trashed', true)
            ->get()
            ->each(function (Message $message) use ($messageDeletionService, &$processed, &$failed): void {
                try {
                    $messageDeletionService->deleteByAdmin($message);
                    $processed++;
                } catch (\Throwable $exception) {
                    report($exception);
                    $failed++;
                }
            });

        $this->clearMessageSelection();
        $this->notifyBulkOutcome($processed, $failed, 0, __('models.trash.notifications.force_deleted'));
    }

    public function openDetails(string $type, int $recordId): void
    {
        $rows = match ($type) {
            'users' => $this->buildUserDetails($recordId),
            'topics' => $this->buildTopicDetails($recordId),
            'messages' => $this->buildMessageDetails($recordId),
            default => null,
        };

        if (!$rows) {
            return;
        }

        $this->detailsTitle = match ($type) {
            'users' => __('models.users.singular'),
            'topics' => __('models.topics.singular'),
            'messages' => __('models.messages.singular'),
            default => '',
        };
        $this->detailsRows = $rows;
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->detailsTitle = '';
        $this->detailsRows = [];
    }

    protected function buildUserDetails(int $userId): ?array
    {
        $user = User::withTrashed()->find($userId);

        if (!$user || !$user->trashed()) {
            return null;
        }

        return [
            ['label' => 'ID', 'value' => (string) $user->id],
            ['label' => __('models.users.fields.name'), 'value' => (string) ($user->name ?? '-')],
            ['label' => __('models.users.fields.surname'), 'value' => (string) ($user->surname ?? '-')],
            ['label' => __('models.users.fields.nickname'), 'value' => (string) ($user->nickname ?? '-')],
            ['label' => __('models.users.fields.email'), 'value' => (string) ($user->email ?? '-')],
            ['label' => __('models.users.fields.phone'), 'value' => (string) ($user->phone ?? '-')],
            ['label' => __('models.users.fields.role'), 'value' => (string) ($user->role ?? '-')],
            ['label' => __('models.users.fields.deleted_at'), 'value' => $this->formatDateTime($user->deleted_at)],
            ['label' => __('models.users.fields.created_at'), 'value' => $this->formatDateTime($user->created_at)],
            ['label' => __('models.users.fields.updated_at'), 'value' => $this->formatDateTime($user->updated_at)],
        ];
    }

    protected function buildTopicDetails(int $topicId): ?array
    {
        $topic = Topic::withTrashed()
            ->with(['category:id,name', 'user:id,name,surname,nickname'])
            ->find($topicId);

        if (!$topic || !$topic->trashed()) {
            return null;
        }

        return [
            ['label' => 'ID', 'value' => (string) $topic->id],
            ['label' => __('models.topics.fields.title'), 'value' => (string) ($topic->title ?? '-')],
            ['label' => __('models.topics.fields.category_id'), 'value' => (string) ($topic->category?->name ?? '-')],
            ['label' => __('models.topics.fields.user_id'), 'value' => (string) ($topic->user?->full_name ?? '-')],
            ['label' => __('models.topics.fields.status'), 'value' => (string) __('models.topics.statuses.' . ($topic->status ?? 'active'))],
            ['label' => __('models.topics.fields.messages_count'), 'value' => (string) ($topic->messages_count ?? 0)],
            ['label' => __('models.topics.fields.deleted_at'), 'value' => $this->formatDateTime($topic->deleted_at)],
            ['label' => __('models.topics.fields.created_at'), 'value' => $this->formatDateTime($topic->created_at)],
            ['label' => __('models.topics.fields.updated_at'), 'value' => $this->formatDateTime($topic->updated_at)],
        ];
    }

    protected function buildMessageDetails(int $messageId): ?array
    {
        $message = Message::query()
            ->withTrashed()
            ->with(['sender:id,name,surname,nickname', 'conversation:id,kind,topic_id', 'conversation.topic:id,title', 'replyTo:id,content'])
            ->find($messageId);

        if (!$message || !$message->is_trashed) {
            return null;
        }

        $conversationContext = $message->conversation?->isTopic()
            ? (string) ($message->conversation?->topic?->title ?? __('models.conversations.kinds.topic'))
            : (string) __('models.conversations.kinds.private');

        return [
            ['label' => __('models.messages.fields.id'), 'value' => (string) $message->id],
            ['label' => __('models.messages.fields.conversation_id'), 'value' => (string) ($message->conversation_id ?? '-')],
            ['label' => __('models.messages.filters.conversation'), 'value' => $conversationContext],
            ['label' => __('models.messages.fields.sender_id'), 'value' => (string) ($message->sender?->full_name ?? '-')],
            ['label' => __('models.messages.fields.reply_to_message_id'), 'value' => (string) ($message->reply_to_message_id ?? '-')],
            ['label' => __('models.messages.fields.content'), 'value' => (string) ($message->content ?? '-')],
            ['label' => __('models.messages.fields.original_content'), 'value' => (string) ($message->original_content ?? '-')],
            ['label' => __('models.messages.fields.edited_content'), 'value' => (string) ($message->edited_content ?? '-')],
            ['label' => __('models.messages.fields.edited_at'), 'value' => $this->formatDateTime($message->edited_at)],
            ['label' => __('models.messages.fields.deleted_at'), 'value' => $this->formatDateTime($message->deleted_at)],
            ['label' => __('models.messages.fields.trashed_at'), 'value' => $this->formatDateTime($message->trashed_at)],
            ['label' => __('models.messages.fields.created_at'), 'value' => $this->formatDateTime($message->created_at)],
            ['label' => __('models.messages.fields.updated_at'), 'value' => $this->formatDateTime($message->updated_at)],
        ];
    }

    protected function applyDeletedUsersSearch($query): void
    {
        $search = trim($this->usersSearch);

        if ($search === '') {
            return;
        }

        if (($id = $this->extractSearchId($search)) !== null) {
            $query->whereKey($id);

            return;
        }

        $query->where(function ($inner) use ($search): void {
            $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('surname', 'like', "%{$search}%")
                ->orWhere('nickname', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    protected function applyDeletedTopicsSearch($query): void
    {
        $search = trim($this->topicsSearch);

        if ($search === '') {
            return;
        }

        if (($id = $this->extractSearchId($search)) !== null) {
            $query->whereKey($id);

            return;
        }

        $query->where(function ($inner) use ($search): void {
            $inner
                ->where('title', 'like', "%{$search}%")
                ->orWhereHas('category', function ($categoryQuery) use ($search): void {
                    $categoryQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function ($userQuery) use ($search): void {
                    $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('nickname', 'like', "%{$search}%");
                });
        });
    }

    protected function applyDeletedMessagesSearch($query): void
    {
        $search = trim($this->messagesSearch);

        if ($search === '') {
            return;
        }

        if (($id = $this->extractSearchId($search)) !== null) {
            $query->whereKey($id);

            return;
        }

        $query->where(function ($inner) use ($search): void {
            $inner
                ->where('content', 'like', "%{$search}%")
                ->orWhere('original_content', 'like', "%{$search}%")
                ->orWhere('edited_content', 'like', "%{$search}%")
                ->orWhere('conversation_id', $search)
                ->orWhere('reply_to_message_id', $search)
                ->orWhereHas('sender', function ($senderQuery) use ($search): void {
                    $senderQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('nickname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        });
    }

    protected function extractSearchId(string $search): ?int
    {
        if (preg_match('/^id:(\d+)$/i', $search, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    protected function formatDateTime(mixed $date): string
    {
        if (!$date) {
            return '-';
        }

        try {
            return $date->format('Y-m-d H:i');
        } catch (\Throwable) {
            return (string) $date;
        }
    }

    protected function notifySuccess(string $title): void
    {
        Notification::make()
            ->success()
            ->title($title)
            ->send();
    }

    protected function notifyWarning(string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->warning()
            ->title($title);

        if (filled($body)) {
            $notification->body((string) $body);
        }

        $notification->send();
    }

    protected function notifyBulkOutcome(int $processed, int $failed, int $skipped, string $successTitle): void
    {
        if ($failed === 0) {
            $this->notifySuccess($successTitle);
            return;
        }

        $this->notifyWarning(
            'ოპერაცია ნაწილობრივ შესრულდა.',
            "წარმატებული: {$processed}, გამოტოვებული: {$skipped}, შეცდომა: {$failed}."
        );
    }

    protected function currentPageUserIds(): array
    {
        return $this->deletedUsers->getCollection()
            ->pluck('id')
            ->map(fn($id): int => (int) $id)
            ->all();
    }

    protected function currentPageTopicIds(): array
    {
        return $this->deletedTopics->getCollection()
            ->pluck('id')
            ->map(fn($id): int => (int) $id)
            ->all();
    }

    protected function currentPageMessageIds(): array
    {
        return $this->deletedMessages->getCollection()
            ->pluck('id')
            ->map(fn($id): int => (int) $id)
            ->all();
    }

    protected function normalizeIds(array $ids): array
    {
        return collect($ids)
            ->filter(fn($id) => filled($id))
            ->map(fn($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    protected function hasFullSelection(array $selectedIds, array $pageIds): bool
    {
        $selected = $this->normalizeIds($selectedIds);
        $currentPage = $this->normalizeIds($pageIds);

        if ($currentPage === []) {
            return false;
        }

        return count(array_diff($currentPage, $selected)) === 0;
    }

    protected function clearSelections(): void
    {
        $this->clearUserSelection();
        $this->clearTopicSelection();
        $this->clearMessageSelection();
    }

    protected function clearUserSelection(): void
    {
        $this->selectAllUsers = false;
        $this->selectedUsers = [];
    }

    protected function clearTopicSelection(): void
    {
        $this->selectAllTopics = false;
        $this->selectedTopics = [];
    }

    protected function clearMessageSelection(): void
    {
        $this->selectAllMessages = false;
        $this->selectedMessages = [];
    }
}
