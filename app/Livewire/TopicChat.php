<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithChatThread;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Topic;
use App\Models\TopicSubscription;
use App\Support\ChatAttachmentRules;
use App\Services\ConversationService;
use App\Services\MessageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class TopicChat extends Component
{
    use WithFileUploads;
    use InteractsWithChatThread;

    /** The topic being viewed/chatted in. */
    public Topic $topic;

    /** Conversation that backs this topic chat (created if missing). */
    public Conversation $conversation;

    public bool $canPost = false; //  Whether the current user is allowed to post messages.

    public bool $composerOpen = false; // composer UI is expanded/open.
    public bool $showUploads = false;

    /** Whether the current user is subscribed to this topic. */
    public bool $isSubscribed = false;

    /**
     * Messages displayed in the UI (already formatted payload from MessageService::listMessages()).
     * Note: stored as array for easy Livewire diffing/patching.
     */
    public array $messages = [];

    /**
     * Cursor used for pagination (load older).
     * We keep both created_at and id to ensure deterministic ordering when multiple messages share same timestamp.
     */
    public ?string $cursorCreatedAt = null;
    public ?int $cursorId = null;

    public bool $hasMore = true; // When false, there are no more older messages to fetch.

    public string $content = ''; // The text being composed by the user

    /**
     * Attachments selected in the composer.
     * Livewire uploads can arrive serialized; we normalize before validation/send.
     *
     * @var array<int, TemporaryUploadedFile|string|array|null>
     */
    public array $attachments = [];
    public int $currentUserId = 0; // current user id (0 if guest).

    /**
     * Validation rules for the composer.
     * - content can be empty if there are attachments
     * - attachment max size is configured in `chat.attachments_max_kb`
     */
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

    /* -------------------------------------------------------------------------
     | Lifecycle
     * ------------------------------------------------------------------------- */

    /**
     * Initialize the component:
     * - load topic + category relation (if missing)
     * - get or create the conversation for this topic
     * - determine current user + subscription state
     * - fetch latest messages
     */
    public function mount(Topic $topic, ConversationService $conversationService, bool $canPost = true): void
    {
        $this->topic = $topic->loadMissing('category');
        $this->conversation = $conversationService->getOrCreateTopicConversation($topic->id);

        $this->canPost = $canPost;
        $this->currentUserId = auth()->id() ?? 0;

        // If logged in, check whether the user is subscribed to this topic.
        if ($this->currentUserId) {
            $this->isSubscribed = TopicSubscription::query()
                ->where('topic_id', $topic->id)
                ->where('user_id', $this->currentUserId)
                ->exists();
        }

        $this->loadLatest();
    }

    /* -------------------------------------------------------------------------
     | UI actions
     * ------------------------------------------------------------------------- */

    /**
     * Open the composer and ensure the user is seeing the newest messages.
     * Also scrolls to bottom (via loadLatest default behavior).
     */
    public function openComposer(): void
    {
        $this->composerOpen = true;
        $this->loadLatest();
    }

    /**
     * Refresh chat messages without forcing scroll-to-bottom.
     */
    public function refresh(): void
    {
        $this->loadLatest(false);
    }

    /**
     * Subscribe/unsubscribe the current user to this topic.
     * - Guests can't subscribe
     * - If subscribed: delete subscription row
     * - Else: create (or restore) subscription row with subscribed_at timestamp
     */
    public function toggleSubscription(): void
    {
        $user = auth()->user();
        if (!$this->currentUserId || !$user) {
            return;
        }

        $this->topic->loadMissing('category');
        if (!$user->can('subscribe', $this->topic)) {
            abort(404);
        }

        if ($this->isSubscribed) {
            TopicSubscription::query()
                ->where('topic_id', $this->topic->id)
                ->where('user_id', $this->currentUserId)
                ->delete();

            $this->isSubscribed = false;
            return;
        }

        TopicSubscription::query()->insertOrIgnore([
            'user_id' => $this->currentUserId,
            'topic_id' => $this->topic->id,
            'subscribed_at' => now(),
        ]);

        $this->isSubscribed = true;
    }

    /* -------------------------------------------------------------------------
     | Message actions
     * ------------------------------------------------------------------------- */

    /**
     * Validate and send a message with optional attachments.
     * - Normalizes uploads (Livewire can serialize them in requests)
     * - Rejects sending if both content and attachments are empty
     * - Clears composer state after successful send
     * - Reloads latest messages (and scrolls to bottom)
     */
    public function sendMessage(MessageService $messageService): void
    {
        // Guests and blocked users can't send.
        if (!$this->canPost || !$this->currentUserId) {
            return;
        }

        if ($this->isRateLimited('topic-chat', 'send', $this->sendRateLimit())) {
            $this->addError('content', 'შეტყობინების გაგზავნა დროებით შეზღუდულია.');
            return;
        }

        $this->resetErrorBag(['content', 'chat']);

        // Ensure attachments are actual TemporaryUploadedFile instances/arrays before validation.
        $this->normalizeAttachments();

        // Validate content + attachments based on rules().
        try {
            $this->validate();
        } catch (ValidationException $exception) {
            $first = collect($exception->errors())->flatten()->first();
            $this->addError('content', (string) ($first ?: 'ვალიდაციის შეცდომა.'));
            return;
        }

        $content = trim($this->content);
        $files = $this->attachments;

        // Prevent empty sends (no text + no files).
        if ($content === '' && empty($files)) {
            $this->addError('content', 'გთხოვთ დაწეროთ მესიჯი ან ატვირთოტთ ფაილი თუ გაგზავნა გსურთ.');
            return;
        }

        // Delegate persistence + file handling to the service layer.
        try {
            $messageService->sendMessage(
                $this->conversation,
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
            $this->addError('chat', 'მესიჯის გაგზავნა ვერ მოხერხდა.');
            return;
        }

        // Reset composer state for a clean UX.
        $this->content = '';
        $this->attachments = [];
        $this->showUploads = false;

        // Posting implies interest; keep UI state consistent.
        $this->isSubscribed = true;

        // Reload to include the newly created message and scroll down.
        $this->loadLatest();
    }

    /**
     * Toggle like/unlike for a given message id.
     * - Updates the DB via MessageService
     * - Updates the local messages array to reflect immediate UI changes
     */
    public function toggleLike(int $messageId, MessageService $messageService): void
    {
        if (!$this->currentUserId || !$this->likeEnabled()) {
            return;
        }

        if ($this->isRateLimited('topic-chat', 'like', $this->likeRateLimit())) {
            $this->addError('chat', 'მოქმედება დროებით შეზღუდულია.');
            return;
        }

        $this->resetErrorBag('chat');

        // Find the message in the current UI list (so we can update it in-place).
        $index = $this->findMessageIndex($messageId);
        if ($index === null) {
            return;
        }

        // Load the canonical Message model (service expects model).
        $message = Message::find($messageId);
        if (!$message) {
            return;
        }

        $liked = (bool) ($this->messages[$index]['liked_by_me'] ?? false);

        // Call service to apply change and return the updated like count.
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

        // Update UI state optimistically with returned count.
        $this->messages[$index]['liked_by_me'] = !$liked;
        $this->messages[$index]['like_count'] = $count;
    }

    /**
     * Soft-delete a message through the service layer.
     * Then updates local UI payload to hide content/attachments without removing the row.
     */
    public function deleteMessage(int $messageId, MessageService $messageService): void
    {
        if (!$this->currentUserId) {
            return;
        }

        if ($this->isRateLimited('topic-chat', 'delete', $this->deleteRateLimit())) {
            $this->addError('chat', 'წაშლა დროებით შეზღუდულია.');
            return;
        }

        $this->resetErrorBag('chat');

        // Use withTrashed in case it was already deleted and we still need to reflect state.
        $message = Message::withTrashed()->find($messageId);
        if (!$message) {
            return;
        }

        // Service enforces deletion behavior.
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

        // Update local array so the UI reflects deletion immediately.
        $index = $this->findMessageIndex($messageId);
        if ($index === null) {
            return;
        }

        $this->messages[$index]['is_deleted'] = true;
        $this->messages[$index]['content'] = null;
        $this->messages[$index]['attachments'] = [];
    }

    /* -------------------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------------------- */

    protected function attachmentMaxKb(): int
    {
        return ChatAttachmentRules::maxKb();
    }

    protected function resolveConversation(): ?Conversation
    {
        return $this->conversation ?? null;
    }

    protected function threadScrollEventName(): string
    {
        return 'topic-chat-scroll-bottom';
    }

    protected function authorizeThread(): bool
    {
        return true;
    }

    protected function likeEnabled(): bool
    {
        return true;
    }

    protected function shouldTrimRenderedThreadMessages(): bool
    {
        return true;
    }

    /* -------------------------------------------------------------------------
     | Rendering
     * ------------------------------------------------------------------------- */

    /**
     * Render the Livewire component view.
     */
    public function render()
    {
        return view('livewire.topic-chat');
    }
}
