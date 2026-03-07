<?php

namespace App\Livewire\Concerns;

use App\Models\Conversation;
use App\Services\MessageService;
use App\Support\ReplyPreviewFormatter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait InteractsWithChatThread
{
    public function loadLatest(bool $scroll = true): void
    {
        if (!$this->authorizeThread()) {
            return;
        }

        $this->cursorCreatedAt = null;
        $this->cursorId = null;
        $this->hasMore = true;

        $messages = $this->fetchThreadMessages(null, null);
        $this->messages = array_reverse($messages);
        $this->refreshActiveReplyContextFromThread();
        $this->updateThreadCursorFromMessages();

        if ($scroll) {
            $this->dispatch($this->threadScrollEventName(), id: $this->getId());
        }
    }

    public function loadOlder(): void
    {
        if (!$this->hasMore || !$this->authorizeThread()) {
            return;
        }

        $messages = $this->fetchThreadMessages($this->cursorCreatedAt, $this->cursorId);
        if (empty($messages)) {
            $this->hasMore = false;
            return;
        }

        $this->messages = array_merge(array_reverse($messages), $this->messages);
        $this->trimRenderedThreadMessages();
        $this->refreshActiveReplyContextFromThread();
        $this->updateThreadCursorFromMessages();
    }

    protected function fetchThreadMessages(?string $cursorCreatedAt, ?int $cursorId, int $limit = 30): array
    {
        $conversation = $this->resolveConversation();
        if (!$conversation) {
            return [];
        }

        /** @var MessageService $service */
        $service = app(MessageService::class);
        $cursor = $cursorCreatedAt ? Carbon::parse($cursorCreatedAt) : null;

        $payload = $service->listMessages(
            $conversation,
            $cursor,
            $cursorId,
            $limit,
            $this->authenticatedUserId()
        );

        return $this->normalizeThreadMessages($payload['messages'] ?? []);
    }

    protected function normalizeThreadMessages(mixed $messages): array
    {
        if ($messages instanceof Collection) {
            return $messages->all();
        }

        return is_array($messages) ? $messages : [];
    }

    protected function updateThreadCursorFromMessages(): void
    {
        if (empty($this->messages)) {
            $this->cursorCreatedAt = null;
            $this->cursorId = null;
            $this->hasMore = false;
            return;
        }

        $oldest = $this->messages[0];
        $createdAt = $oldest['created_at'] ?? null;
        if ($createdAt instanceof Carbon) {
            $createdAt = $createdAt->toDateTimeString();
        }

        $this->cursorCreatedAt = $createdAt ? (string) $createdAt : null;
        $this->cursorId = isset($oldest['id']) ? (int) $oldest['id'] : null;
    }

    protected function findMessageIndex(int $messageId): ?int
    {
        foreach ($this->messages as $index => $message) {
            if ((int) ($message['id'] ?? 0) === $messageId) {
                return $index;
            }
        }

        return null;
    }

    protected function normalizeAttachments(): void
    {
        $value = TemporaryUploadedFile::unserializeFromLivewireRequest($this->attachments);

        if ($value === null) {
            $this->attachments = [];
            return;
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $this->attachments = array_values(array_filter($value));
    }

    protected function trimRenderedThreadMessages(): void
    {
        if (!$this->shouldTrimRenderedThreadMessages()) {
            return;
        }

        $max = max(30, (int) config('chat.max_rendered_messages', 200));
        if (count($this->messages) <= $max) {
            return;
        }

        $this->messages = array_slice($this->messages, 0, $max);
    }

    protected function sendRateLimit(): int
    {
        return max(1, (int) config('chat.rate_limits.send_per_minute', 10));
    }

    protected function likeRateLimit(): int
    {
        return max(1, (int) config('chat.rate_limits.like_per_minute', 60));
    }

    protected function deleteRateLimit(): int
    {
        return max(1, (int) config('chat.rate_limits.delete_per_minute', 30));
    }

    protected function editRateLimit(): int
    {
        return max(1, (int) config('chat.rate_limits.edit_per_minute', 20));
    }

    protected function rateLimitDecaySeconds(): int
    {
        return max(1, (int) config('chat.rate_limits.window_seconds', 60));
    }

    protected function isRateLimited(string $keyPrefix, string $action, int $maxAttempts): bool
    {
        $key = $keyPrefix . ':' . $action . ':' . $this->authenticatedUserId() . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return true;
        }

        RateLimiter::hit($key, $this->rateLimitDecaySeconds());

        return false;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{id:int,author:string,content:string}|null
     */
    protected function buildReplyContext(array $payload): ?array
    {
        return app(ReplyPreviewFormatter::class)->buildContextFromPayload(
            $payload,
            $this->authenticatedUserId()
        );
    }

    /**
     * Keep reply previews synchronized when the parent message changes locally
     * (edit/delete) without requiring a full thread reload.
     *
     * @param array<string, mixed> $parentPayload
     */
    protected function syncReplyReferencesForParentPayload(array $parentPayload): void
    {
        $parentId = (int) ($parentPayload['id'] ?? 0);
        if ($parentId <= 0) {
            return;
        }

        $formatter = app(ReplyPreviewFormatter::class);
        $attachments = is_array($parentPayload['attachments'] ?? null)
            ? $parentPayload['attachments']
            : [];
        $isDeleted = (bool) ($parentPayload['is_deleted'] ?? false);

        $replyPayload = [
            'id' => $parentId,
            'is_deleted' => $isDeleted,
            'content' => $isDeleted ? null : ($parentPayload['content'] ?? null),
            'content_preview' => $formatter->formatContentPreview(
                $parentPayload['content'] ?? null,
                count($attachments),
                $isDeleted,
                140
            ),
            'sender' => is_array($parentPayload['sender'] ?? null) ? $parentPayload['sender'] : null,
        ];

        foreach ($this->messages as $index => $messagePayload) {
            $replyTo = is_array($messagePayload['reply_to'] ?? null)
                ? $messagePayload['reply_to']
                : null;

            if ((int) ($replyTo['id'] ?? 0) !== $parentId) {
                continue;
            }

            $this->messages[$index]['reply_to'] = $replyPayload;
        }

        if ((int) ($this->replyToMessageId ?? 0) === $parentId) {
            if ($isDeleted) {
                $this->cancelReply();
                return;
            }

            $this->replyToContext = $this->buildReplyContext($parentPayload);
        }
    }

    protected function authenticatedUserId(): int
    {
        $id = auth()->id();

        if ($id) {
            return (int) $id;
        }

        return (int) ($this->currentUserId ?? 0);
    }

    protected function refreshActiveReplyContextFromThread(): void
    {
        $replyToMessageId = (int) ($this->replyToMessageId ?? 0);
        if ($replyToMessageId <= 0) {
            return;
        }

        $index = $this->findMessageIndex($replyToMessageId);
        if ($index === null) {
            return;
        }

        $payload = $this->messages[$index] ?? [];
        if ((bool) ($payload['is_deleted'] ?? false)) {
            $this->cancelReply();
            return;
        }

        $this->replyToContext = $this->buildReplyContext($payload);
    }

    protected function authorizeThread(): bool
    {
        return true;
    }

    protected function likeEnabled(): bool
    {
        return true;
    }

    abstract protected function resolveConversation(): ?Conversation;

    abstract protected function threadScrollEventName(): string;

    protected function shouldTrimRenderedThreadMessages(): bool
    {
        return false;
    }
}
