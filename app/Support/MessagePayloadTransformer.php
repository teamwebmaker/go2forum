<?php

namespace App\Support;

use App\Models\Message;
use App\Models\MessageAttachment;
use Illuminate\Support\Facades\Storage;

class MessagePayloadTransformer
{
    public function transform(
        Message $message,
        ?int $currentUserId = null,
        int $likeCount = 0,
        bool $likedByMe = false
    ): array {
        $message->loadMissing(['sender', 'attachments', 'conversation']);

        $sender = $message->sender;
        $isDeleted = $message->trashed();
        $isPrivateConversation = (bool) $message->conversation?->isPrivate();
        $senderFullName = $sender?->full_name ?? $sender?->name;
        $canEdit = $message->isEditableBy($currentUserId);

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
            ],
            'author_label' => ($currentUserId && (int) ($sender?->id ?? 0) === $currentUserId)
                ? 'მე'
                : ($senderFullName ?? 'User'),
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
}
