@props([
    'message' => [],
    'currentUserId' => 0,
    'canReply' => true,
    'variant' => 'topic', // topic|private
    'editingMessageId' => null,
])

@php
    $isTopic = $variant === 'topic';
    $isMine = (int) ($message['sender']['id'] ?? 0) === (int) $currentUserId;
    $isDeleted = (bool) ($message['is_deleted'] ?? false);
    $isEdited = (bool) ($message['is_edited'] ?? false);
    $canEdit = $isTopic && $isMine && (bool) ($message['can_edit'] ?? false) && !$isDeleted;
    $isEditing = $isTopic && (int) ($editingMessageId ?? 0) === (int) ($message['id'] ?? 0);
    $likeCount = (int) ($message['like_count'] ?? 0);
    $likedByMe = (bool) ($message['liked_by_me'] ?? false);

    $sender = $message['sender'] ?? [];
    $senderFullName = trim((string) ($sender['full_name'] ?? $sender['name'] ?? 'User'));
    $senderNickname = trim((string) ($sender['nickname'] ?? ''));
    $senderStatusLabel = trim((string) ($sender['status_label'] ?? ''));
    $senderId = (int) ($sender['id'] ?? 0);
    $primaryLabel = $senderNickname !== '' ? $senderNickname : $senderFullName;
    $secondaryLabel = $isMine ? null : ($senderNickname !== '' && $senderFullName !== '' ? $senderFullName : null);
    $avatarUrl = (string) ($sender['avatar'] ?? '');
    $badgeIcon = $sender['badge_icon'] ?? null;
    $badgeColor = $sender['badge_color'] ?? '';
    $createdAt = (string) ($message['created_at_label'] ?? '');
    $replyTo = is_array($message['reply_to'] ?? null) ? $message['reply_to'] : null;
    $replyToDeleted = (bool) ($replyTo['is_deleted'] ?? false);
    $replySender = is_array($replyTo['sender'] ?? null) ? $replyTo['sender'] : [];
    $replySenderFullName = trim((string) ($replySender['full_name'] ?? $replySender['name'] ?? ''));
    $replySenderNickname = trim((string) ($replySender['nickname'] ?? ''));
    $replySenderId = (int) ($replySender['id'] ?? 0);
    $replyAuthorLabel = $replySenderNickname !== ''
        ? $replySenderNickname
        : ($replySenderFullName !== '' ? $replySenderFullName : ($replySenderId === (int) $currentUserId ? 'მე' : 'მომხმარებელი'));
    $replyPreview = trim((string) ($replyTo['content_preview'] ?? $replyTo['content'] ?? ''));
    $replyPreviewText = $replyPreview !== '' ? $replyPreview : 'დანართი';

    $rootClasses = $isTopic
        ? ['flex', $isMine ? 'justify-end' : 'justify-start']
        : ['w-full'];

    $articleClasses = $isTopic
        ? 'w-full max-w-full sm:w-[88%] sm:max-w-2xl rounded-2xl border px-3 py-2.5 sm:px-4 sm:py-3 shadow-sm ' .
            ($isMine ? 'bg-primary-50/40 border-primary-200' : 'bg-white border-slate-200')
        : 'min-w-0 w-full max-w-[94%] sm:max-w-[78%] rounded-2xl border px-3 py-2 shadow-sm ' .
            ($isMine ? 'ml-auto border-primary-200 bg-primary-50/40' : 'mr-auto border-slate-200 bg-white');

    $avatarSize = $isTopic ? 'h-9 w-9 text-xs' : 'h-8 w-8 text-[11px]';
    $privateChatUrl = null;
    if ($isTopic && $currentUserId && !$isMine && $senderId > 0 && $senderNickname !== '') {
        $privateChatUrl = route('profile.messages', ['recipient' => $senderNickname]);
    }
@endphp

<div {{ $attributes->class($rootClasses) }}>
    <article class="{{ $articleClasses }}">
        <div class="{{ $isTopic ? 'flex flex-col gap-2 text-[11px] text-slate-500 sm:flex-row sm:items-start sm:justify-between sm:gap-3' : 'mb-2 flex flex-col gap-2 text-[11px] text-slate-500 sm:flex-row sm:items-start sm:justify-between sm:gap-3' }}">
            <div class="flex min-w-0  items-start gap-2">
                <x-chat.user-identity :name="$primaryLabel" :secondary="$secondaryLabel"
                    :avatar="$avatarUrl !== '' ? $avatarUrl : null" :badgeIcon="$badgeIcon" :badgeColor="$badgeColor"
                    :avatarAlt="$senderFullName !== '' ? $senderFullName : $primaryLabel" :avatarSizeClass="$avatarSize"
                    :statusLabel="$senderStatusLabel !== '' ? $senderStatusLabel : null"
                    wrapperClass="flex min-w-0 flex-1 items-start gap-2" textWrapperClass="min-w-0"
                    nameClass="truncate text-sm font-semibold text-slate-800" secondaryClass="truncate text-xs text-slate-500" />

                @if ($privateChatUrl)
                    <a href="{{ $privateChatUrl }}"
                        class="inline-flex size-7 shrink-0 items-center justify-center rounded-full border border-slate-200 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                        title="პირადი ჩატი">
                        <x-app-icon name="chat-bubble-left-ellipsis" class="size-4" />
                    </a>
                @endif
            </div>

            @if ($isTopic)
                <span class="inline-flex shrink-0 self-end items-center gap-1 tabular-nums text-[10px] sm:self-auto sm:text-[11px]">
                    <span>{{ $createdAt }}</span>
                    @if ($isEdited)
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-500">ჩასწორებული</span>
                    @endif
                </span>
            @else
                <div class="shrink-0 self-end text-[10px] sm:self-auto sm:text-[11px]">{{ $createdAt }}</div>
            @endif
        </div>

        @include('components.chat.message-card.content')
        @include('components.chat.message-card.attachments')
        @include('components.chat.message-card.actions')
    </article>
</div>
