<?php

namespace App\Support;

use App\Models\Message;
use App\Models\MessageAttachment;
use Illuminate\Support\Facades\Storage;

class MessagePayloadTransformer
{
    public function __construct(
        protected ReplyPreviewFormatter $replyPreviewFormatter
    ) {
    }

    public function transform(
        Message $message,
        ?int $currentUserId = null,
        int $likeCount = 0,
        bool $likedByMe = false
    ): array {
        $message->loadMissing(['sender', 'attachments', 'conversation', 'replyTo.sender', 'replyTo.attachments']);

        $sender = $message->sender;
        $isDeleted = $message->trashed();
        $isPrivateConversation = (bool) $message->conversation?->isPrivate();
        $senderFullName = $sender?->full_name ?? $sender?->name;
        $canEdit = $message->isEditableBy($currentUserId);
        $replyTo = $message->replyTo;
        $replySender = $replyTo?->sender;
        $replySenderFullName = $replySender?->full_name ?? $replySender?->name;
        $replyAttachmentCount = $replyTo ? (int) $replyTo->attachments->count() : 0;
        $replyIsDeleted = (bool) $replyTo?->trashed();
        $replyContent = $replyIsDeleted
            ? null
            : $this->normalizeReplyContent($replyTo?->content);
        $replyPreview = $replyTo
            ? $this->replyPreviewFormatter->formatContentPreview(
                $replyTo->content,
                $replyAttachmentCount,
                $replyIsDeleted,
                140
            )
            : null;

        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender' => [
                'id' => $sender?->id,
                'name' => $senderFullName,
                'full_name' => $senderFullName,
                'nickname' => $sender?->nickname,
                'avatar' => $sender?->avatar_url,
                'badge_icon' => BadgeColors::iconForUser($sender),
                'badge_color' => BadgeColors::forUser($sender),
                'status_label' => $this->statusLabelForUser($sender),
            ],
            'author_label' => ($currentUserId && (int) ($sender?->id ?? 0) === $currentUserId)
                ? 'მე'
                : ($senderFullName ?? 'User'),
            'reply_to' => $replyTo ? [
                'id' => $replyTo->id,
                'is_deleted' => $replyIsDeleted,
                'content' => $replyContent,
                'content_preview' => $replyPreview,
                'sender' => [
                    'id' => $replySender?->id,
                    'name' => $replySenderFullName,
                    'full_name' => $replySenderFullName,
                    'nickname' => $replySender?->nickname,
                    'avatar' => $replySender?->avatar_url,
                    'badge_icon' => BadgeColors::iconForUser($replySender),
                    'badge_color' => BadgeColors::forUser($replySender),
                    'status_label' => $this->statusLabelForUser($replySender),
                ],
            ] : null,
            'content' => $isDeleted ? null : $message->content,
            'created_at' => $message->created_at?->toISOString(),
            'created_at_label' => $message->created_at?->format('m/d/Y, h:ia'),
            'edited_at' => $message->edited_at?->toISOString(),
            'edited_at_label' => $message->edited_at?->format('m/d/Y, h:ia'),
            'is_edited' => !is_null($message->edited_at),
            'attachments' => $isDeleted
                ? []
                : $message->attachments->map(function (MessageAttachment $attachment) use ($isPrivateConversation) {
                    $attachmentUrl = $this->attachmentUrl($attachment, $isPrivateConversation);

                    return [
                        'id' => $attachment->id,
                        'type' => $attachment->attachment_type,
                        'url' => $attachmentUrl,
                        'download_url' => $isPrivateConversation ? $attachmentUrl : null,
                        'path' => $attachment->path,
                        'original_name' => $attachment->original_name,
                        'mime_type' => $attachment->mime_type,
                        'size_bytes' => $attachment->size_bytes,
                    ];
                })->values()->all(),
            'is_deleted' => $isDeleted,
            'like_count' => max(0, $likeCount),
            'liked_by_me' => $currentUserId ? $likedByMe : false,
            'can_edit' => $isDeleted ? false : $canEdit,
        ];
    }

    protected function attachmentUrl(MessageAttachment $attachment, bool $isPrivateConversation): string
    {
        if ($isPrivateConversation) {
            return route('messages.attachments.download', ['attachment' => $attachment->id]);
        }

        if ($attachment->disk === 'public') {
            return '/storage/' . ltrim(string: $attachment->path, characters: '/');
        }

        return Storage::disk($attachment->disk)->url($attachment->path);
    }

    protected function normalizeReplyContent(?string $content): ?string
    {
        $content = is_string($content) ? trim($content) : null;
        return $content === '' ? null : $content;
    }

    protected function statusLabelForUser($user): ?string
    {
        if (!$user) {
            return null;
        }

        if ((bool) ($user->is_expert ?? false)) {
            return 'ექსპერტი';
        }

        if ((bool) ($user->is_top_commentator ?? false)) {
            return 'ტოპ კომენტატორი';
        }

        return null;
    }
}
