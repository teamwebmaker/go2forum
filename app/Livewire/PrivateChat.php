<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithChatThread;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Settings;
use App\Models\User;
use App\Support\ChatAttachmentRules;
use App\Support\BadgeColors;
use App\Services\ConversationService;
use App\Services\MessageService;
use App\Services\MessageServiceSupport;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class PrivateChat extends Component
{
    use WithFileUploads;
    use InteractsWithChatThread;

    public int $currentUserId = 0;
    public bool $isCurrentUserVerified = false;
    public bool $enforceRecipientVerification = false;
    public bool $chatOpen = false;

    public string $recipientEmail = '';
    public ?int $recipientId = null;

    /** @var array{id:int,name:string,avatar:?string,is_email_verified:bool,badge_color:?string}|null */
    public ?array $recipientPreview = null;
    /** @var array{id:int,name:string,avatar:?string,is_email_verified:bool,badge_color:?string}|null */
    public ?array $activeRecipient = null;
    public ?int $activeRecipientId = null;

    public ?int $selectedConversationId = null;

    /** @var array<int, array<string, mixed>> */
    public array $conversations = [];

    /** @var array<int, array<string, mixed>> */
    public array $messages = [];

    public ?string $cursorCreatedAt = null;
    public ?int $cursorId = null;
    public bool $hasMore = true;
    public bool $showUploads = false;

    public string $content = '';
    /** @var array<int, TemporaryUploadedFile|string|array|null> */
    public array $attachments = [];

    protected function rules(): array
    {
        return array_merge(
            ['content' => ['nullable', 'string']],
            ChatAttachmentRules::rules('attachments')
        );
    }

    protected function messages(): array
    {
        return ChatAttachmentRules::messages('attachments');
    }

    public function mount(?int $initialConversationId = null, ConversationService $conversationService): void
    {
        $user = auth()->user();
        $this->currentUserId = $user?->id ?? 0;
        $this->isCurrentUserVerified = $user ? (bool) $user->isVerified() : false;
        $this->enforceRecipientVerification = Settings::shouldEmailVerify();

        if (!$this->isCurrentUserVerified) {
            return;
        }

        $this->loadConversations($conversationService);

        if ($initialConversationId) {
            $this->openConversation($initialConversationId, $conversationService);
            return;
        }

        if (!empty($this->conversations)) {
            $this->openConversation((int) $this->conversations[0]['id'], $conversationService);
        }
    }

    public function findRecipient(): void
    {
        $this->resetErrorBag(['recipientEmail', 'content', 'chat']);

        if (!$this->ensureVerifiedUser()) {
            return;
        }

        $key = 'private-chat-lookup:' . $this->currentUserId . '|' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 12)) {
            $this->addError('recipientEmail', 'მიმღების მოძიების მცდელობა დროებით შეზღუდულია.');
            return;
        }
        RateLimiter::hit($key, 60);

        $this->validate([
            'recipientEmail' => ['required', 'email:rfc'],
        ]);

        $email = mb_strtolower(trim($this->recipientEmail));

        $recipient = User::query()
            ->select(['id', 'name', 'surname', 'image', 'email_verified_at', 'is_expert', 'is_top_commentator'])
            ->where('email', $email)
            ->when(
                $this->enforceRecipientVerification,
                fn($query) => $query->whereNotNull('email_verified_at')
            )
            ->whereKeyNot($this->currentUserId)
            ->first();

        if (!$recipient) {
            $this->clearRecipient();
            $this->addError('recipientEmail', 'მიმღები ვერ მოიძებნა.');
            return;
        }

        $this->recipientId = $recipient->id;
        $this->recipientPreview = [
            'id' => $recipient->id,
            'name' => $recipient->full_name ?? $recipient->name,
            'avatar' => $recipient->avatar_url,
            'is_email_verified' => !is_null($recipient->email_verified_at),
            'badge_color' => BadgeColors::forUser($recipient),
        ];
    }

    public function startConversation(ConversationService $conversationService): void
    {
        $this->resetErrorBag(['recipientEmail', 'content', 'chat']);

        if (!$this->ensureVerifiedUser()) {
            return;
        }

        if (!$this->recipientId) {
            $this->addError('recipientEmail', 'ჯერ მოძებნეთ მომხმარებელი ელ.ფოსტით.');
            return;
        }

        if (
            $this->enforceRecipientVerification &&
            !($this->recipientPreview['is_email_verified'] ?? false)
        ) {
            $this->addError('chat', 'მიმოწერა ამ დროისთვის ხელმისაწვდომი არ არის.');
            return;
        }

        $this->chatOpen = true;
        $this->activeRecipientId = $this->recipientId;
        $this->activeRecipient = $this->recipientPreview;
        $this->clearRecipient();
        $this->recipientEmail = '';
        $this->resetThreadState();

        $existingConversation = $this->findConversationWithRecipient($this->activeRecipientId);
        if ($existingConversation) {
            $this->selectedConversationId = $existingConversation->id;
            $this->loadLatest();
            return;
        }

        $this->hasMore = false;
        $this->loadConversations($conversationService);
    }

    public function openConversation(int $conversationId, ConversationService $conversationService): void
    {
        if (!$this->ensureVerifiedUser()) {
            return;
        }

        $conversation = $this->resolvePrivateConversation($conversationId);
        if (!$conversation) {
            return;
        }

        $this->chatOpen = true;
        $this->selectedConversationId = $conversation->id;
        $this->syncActiveRecipientFromConversation($conversation);
        $this->clearRecipient();
        $this->resetComposerState();
        $this->recipientEmail = '';
        $this->loadLatest();
        $this->loadConversations($conversationService);
    }

    public function sendMessage(
        MessageService $messageService,
        ConversationService $conversationService
    ): void {
        $this->resetErrorBag(['content', 'attachments', 'attachments.*', 'chat']);

        if (!$this->ensureVerifiedUser()) {
            return;
        }

        if ($this->isRateLimited('private-chat', 'send', $this->sendRateLimit())) {
            $this->addError('content', 'შეტყობინების გაგზავნა დროებით შეზღუდულია.');
            return;
        }

        $this->normalizeAttachments();

        try {
            $this->validate();
        } catch (ValidationException $exception) {
            $first = collect($exception->errors())->flatten()->first();
            $this->addError('content', (string) ($first ?: 'ვალიდაციის შეცდომა.'));
            return;
        }

        $content = trim($this->content);
        $files = $this->attachments;
        if ($content === '' && empty($files)) {
            $this->addError('content', 'შეიყვანეთ შეტყობინება ან ატვირთეთ ფაილი.');
            return;
        }

        $conversation = $this->resolvePrivateConversation($this->selectedConversationId);

        if (!$conversation) {
            if (!$this->activeRecipientId) {
                $this->addError('chat', 'აირჩიეთ ან მოძებნეთ მიმღები.');
                return;
            }

            try {
                $conversation = $conversationService->getOrCreatePrivateConversation(
                    $this->currentUserId,
                    $this->activeRecipientId
                );
            } catch (\Throwable $exception) {
                report($exception);
                $this->addError('chat', 'პირადი ჩატის გახსნა ვერ მოხერხდა.');
                return;
            }

            $this->selectedConversationId = $conversation->id;
        }

        try {
            $messageService->sendMessage(
                $conversation,
                $this->currentUserId,
                $content !== '' ? $content : null,
                $files
            );
        } catch (ValidationException $exception) {
            $first = collect($exception->errors())->flatten()->first();
            $this->addError('content', (string) ($first ?: 'ვალიდაციის შეცდომა.'));
            return;
        } catch (AuthorizationException $exception) {
            $this->addError('chat', $exception->getMessage());
            return;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('chat', 'შეტყობინების გაგზავნა ვერ მოხერხდა.');
            return;
        }

        $this->chatOpen = true;
        $this->resetComposerState();
        $this->loadLatest();
        $this->loadConversations($conversationService);
    }

    public function toggleLike(int $messageId, MessageService $messageService): void
    {
        if (!$this->currentUserId || !$this->likeEnabled()) {
            return;
        }

        if ($this->isRateLimited('private-chat', 'like', $this->likeRateLimit())) {
            $this->addError('chat', 'მოქმედება დროებით შეზღუდულია.');
            return;
        }

        $this->resetErrorBag('chat');

        $index = $this->findMessageIndex($messageId);
        if ($index === null) {
            return;
        }

        $message = Message::find($messageId);
        if (!$message || $message->conversation_id !== $this->selectedConversationId) {
            return;
        }

        $liked = (bool) ($this->messages[$index]['liked_by_me'] ?? false);

        try {
            $count = $liked
                ? $messageService->unlikeMessage($message, $this->currentUserId)
                : $messageService->likeMessage($message, $this->currentUserId);
        } catch (AuthorizationException $exception) {
            $this->addError('chat', $exception->getMessage());
            return;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('chat', 'მოქმედება ვერ შესრულდა.');
            return;
        }

        $this->messages[$index]['liked_by_me'] = !$liked;
        $this->messages[$index]['like_count'] = $count;
    }

    public function deleteMessage(int $messageId, MessageService $messageService): void
    {
        if (!$this->currentUserId) {
            return;
        }

        if ($this->isRateLimited('private-chat', 'delete', $this->deleteRateLimit())) {
            $this->addError('chat', 'წაშლა დროებით შეზღუდულია.');
            return;
        }

        $this->resetErrorBag('chat');

        $message = Message::withTrashed()->find($messageId);
        if (!$message || $message->conversation_id !== $this->selectedConversationId) {
            return;
        }

        try {
            $messageService->deleteMessage($message, $this->currentUserId, false);
        } catch (AuthorizationException $exception) {
            $this->addError('chat', $exception->getMessage());
            return;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('chat', 'მესიჯის წაშლა ვერ მოხერხდა.');
            return;
        }

        $index = $this->findMessageIndex($messageId);
        if ($index === null) {
            return;
        }

        $this->messages[$index]['is_deleted'] = true;
        $this->messages[$index]['content'] = null;
        $this->messages[$index]['attachments'] = [];
    }

    protected function loadConversations(ConversationService $conversationService): void
    {
        $conversations = $conversationService->listForUser($this->currentUserId)
            ->filter(fn(Conversation $conversation) => $conversation->isPrivate())
            ->values();

        $this->conversations = $conversations
            ->map(function (Conversation $conversation) {
                $otherUser = $this->resolveOtherUser($conversation);

                return [
                    'id' => $conversation->id,
                    'other_user' => $otherUser ? [
                        'id' => $otherUser->id,
                        'name' => $otherUser->full_name ?? $otherUser->name,
                        'avatar' => $otherUser->avatar_url,
                        'badge_color' => BadgeColors::forUser($otherUser),
                    ] : null,
                    'last_message_at' => $conversation->last_message_at,
                ];
            })
            ->values()
            ->all();
    }

    protected function resolvePrivateConversation(?int $conversationId): ?Conversation
    {
        if (!$conversationId) {
            return null;
        }

        $conversation = Conversation::query()
            ->whereKey($conversationId)
            ->where('kind', Conversation::KIND_PRIVATE)
            ->first();

        if (!$conversation) {
            return null;
        }

        try {
            app(MessageServiceSupport::class)->authorizeConversationRead(
                $conversation,
                $this->currentUserId
            );
        } catch (AuthorizationException) {
            return null;
        }

        return $conversation;
    }

    protected function syncActiveRecipientFromConversation(Conversation $conversation): void
    {
        $otherUser = $this->resolveOtherUser($conversation);
        if (!$otherUser) {
            return;
        }

        $this->activeRecipientId = $otherUser->id;
        $this->activeRecipient = [
            'id' => $otherUser->id,
            'name' => $otherUser->full_name ?? $otherUser->name,
            'avatar' => $otherUser->avatar_url,
            'is_email_verified' => !is_null($otherUser->email_verified_at),
            'badge_color' => BadgeColors::forUser($otherUser),
        ];
    }

    protected function resolveOtherUser(Conversation $conversation): ?User
    {
        if (!$conversation->isPrivate()) {
            return null;
        }

        $conversation->loadMissing([
            'directUser1:id,name,surname,image,email_verified_at,is_expert,is_top_commentator',
            'directUser2:id,name,surname,image,email_verified_at,is_expert,is_top_commentator',
        ]);

        return $conversation->direct_user1_id === $this->currentUserId
            ? $conversation->directUser2
            : $conversation->directUser1;
    }

    protected function clearRecipient(): void
    {
        $this->recipientId = null;
        $this->recipientPreview = null;
    }

    protected function resetThreadState(): void
    {
        $this->selectedConversationId = null;
        $this->messages = [];
        $this->cursorCreatedAt = null;
        $this->cursorId = null;
        $this->resetComposerState();
    }

    protected function findConversationWithRecipient(?int $recipientId): ?Conversation
    {
        if (!$recipientId) {
            return null;
        }

        $user1Id = min($this->currentUserId, $recipientId);
        $user2Id = max($this->currentUserId, $recipientId);

        return Conversation::query()
            ->where('kind', Conversation::KIND_PRIVATE)
            ->where('direct_user1_id', $user1Id)
            ->where('direct_user2_id', $user2Id)
            ->first();
    }

    protected function ensureVerifiedUser(): bool
    {
        if ($this->isCurrentUserVerified) {
            return true;
        }

        $this->addError('chat', 'პირადი ჩატი ხელმისაწვდომია მხოლოდ ვერიფიცირებული მომხმარებლისთვის.');
        return false;
    }

    protected function attachmentMaxKb(): int
    {
        return ChatAttachmentRules::maxKb();
    }

    protected function resolveConversation(): ?Conversation
    {
        return $this->resolvePrivateConversation($this->selectedConversationId);
    }

    protected function threadScrollEventName(): string
    {
        return 'private-chat-scroll-bottom';
    }

    protected function authorizeThread(): bool
    {
        return $this->isCurrentUserVerified;
    }

    protected function likeEnabled(): bool
    {
        return true;
    }

    protected function resetComposerState(): void
    {
        $this->content = '';
        $this->attachments = [];
        $this->showUploads = false;
    }

    public function render()
    {
        return view('livewire.private-chat');
    }
}
