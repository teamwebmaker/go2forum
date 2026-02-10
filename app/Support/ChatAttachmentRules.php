<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class ChatAttachmentRules
{
    public static function maxCount(): int
    {
        return max(1, (int) config('chat.attachments_max_count', 5));
    }

    public static function maxKb(): int
    {
        return max(1, (int) config('chat.attachments_max_kb', 2048));
    }

    public static function acceptAttribute(): string
    {
        return (string) config('chat.attachments_accept', 'image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt,.zip');
    }

    /**
     * @return array<int, string>
     */
    public static function documentMimes(): array
    {
        $configured = config('chat.attachments_document_mimes', []);
        if (!is_array($configured) || empty($configured)) {
            return [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain',
                'application/zip',
                'application/x-zip-compressed',
            ];
        }

        return array_values(array_filter(array_map('strval', $configured)));
    }

    /**
     * @param array<int, string>|null $documentMimes
     */
    public static function isSupportedMime(string $mime, ?array $documentMimes = null): bool
    {
        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        $allowed = $documentMimes ?? static::documentMimes();
        return in_array($mime, $allowed, true);
    }

    public static function fileRule(?array $documentMimes = null): array
    {
        return [
            'file',
            'max:' . static::maxKb(),
            function (string $attribute, mixed $value, callable $fail) use ($documentMimes): void {
                if (!$value instanceof UploadedFile) {
                    return;
                }

                $mime = (string) ($value->getMimeType() ?: '');
                if (static::isSupportedMime($mime, $documentMimes)) {
                    return;
                }

                $fail('The selected file type is not supported.');
            },
        ];
    }

    public static function rules(string $field = 'attachments'): array
    {
        return [
            $field => ['nullable', 'array', 'max:' . static::maxCount()],
            $field . '.*' => static::fileRule(),
        ];
    }

    public static function maxCountMessage(): string
    {
        return 'შეგიძლიათ მაქსიმუმ :max ფაილის ატვირთვა.';
    }

    public static function messages(string $field = 'attachments'): array
    {
        return [
            $field . '.max' => static::maxCountMessage(),
        ];
    }
}
