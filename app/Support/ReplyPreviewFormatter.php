<?php

namespace App\Support;

use Illuminate\Support\Str;

class ReplyPreviewFormatter
{
    public const UNKNOWN_AUTHOR = 'მომხმარებელი';
    public const SELF_AUTHOR = 'მე';
    public const ATTACHMENT_PLACEHOLDER = 'დანართი';
    public const DELETED_PLACEHOLDER = 'ეს მესიჯი წაშლილია.';

    public function buildContextFromPayload(array $payload, ?int $currentUserId = null): ?array
    {
        $messageId = (int) ($payload['id'] ?? 0);
        if ($messageId <= 0) {
            return null;
        }

        $sender = is_array($payload['sender'] ?? null) ? $payload['sender'] : [];
        $author = $this->formatAuthorLabel(
            (int) ($sender['id'] ?? 0),
            $sender['nickname'] ?? null,
            $sender['full_name'] ?? $sender['name'] ?? null,
            $currentUserId
        );

        $attachments = is_array($payload['attachments'] ?? null)
            ? $payload['attachments']
            : [];
        $content = $this->formatContentPreview(
            $payload['content'] ?? null,
            count($attachments),
            (bool) ($payload['is_deleted'] ?? false),
            140
        ) ?? self::ATTACHMENT_PLACEHOLDER;

        return [
            'id' => $messageId,
            'author' => $author,
            'content' => $content,
        ];
    }

    public function formatAuthorLabel(
        int $senderId,
        ?string $senderNickname,
        ?string $senderFullName,
        ?int $currentUserId = null
    ): string {
        if ($currentUserId && $senderId > 0 && $senderId === $currentUserId) {
            return self::SELF_AUTHOR;
        }

        $nickname = $this->normalizeString($senderNickname);
        if ($nickname !== null) {
            return $nickname;
        }

        $fullName = $this->normalizeString($senderFullName);
        if ($fullName !== null) {
            return $fullName;
        }

        return self::UNKNOWN_AUTHOR;
    }

    public function formatContentPreview(
        mixed $content,
        int $attachmentCount = 0,
        bool $isDeleted = false,
        int $limit = 140
    ): ?string {
        if ($isDeleted) {
            return self::DELETED_PLACEHOLDER;
        }

        $normalized = $this->normalizeString(is_string($content) ? $content : null);
        if ($normalized !== null) {
            return Str::limit($normalized, $limit);
        }

        if ($attachmentCount > 0) {
            return self::ATTACHMENT_PLACEHOLDER;
        }

        return null;
    }

    protected function normalizeString(?string $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        return $trimmed === '' ? null : $trimmed;
    }
}
