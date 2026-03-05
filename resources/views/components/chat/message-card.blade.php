@props([
    'message' => [],
    'currentUserId' => 0,
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
    $primaryLabel = $isMine ? 'მე' : ($senderNickname !== '' ? $senderNickname : $senderFullName);
    $secondaryLabel = $isMine ? null : ($senderNickname !== '' && $senderFullName !== '' ? $senderFullName : null);
    $avatarUrl = (string) ($sender['avatar'] ?? '');
    $badgeIcon = $sender['badge_icon'] ?? null;
    $badgeColor = $sender['badge_color'] ?? '';
    $createdAt = (string) ($message['created_at_label'] ?? '');

    $rootClasses = $isTopic
        ? ['flex', $isMine ? 'justify-end' : 'justify-start']
        : ['w-full'];

    $articleClasses = $isTopic
        ? 'w-full max-w-full sm:w-[88%] sm:max-w-2xl rounded-2xl border px-3 py-2.5 sm:px-4 sm:py-3 shadow-sm ' .
            ($isMine ? 'bg-primary-50/40 border-primary-200' : 'bg-white border-slate-200')
        : 'min-w-0 w-full max-w-[94%] sm:max-w-[78%] rounded-2xl border px-3 py-2 shadow-sm ' .
            ($isMine ? 'ml-auto border-primary-200 bg-primary-50/40' : 'mr-auto border-slate-200 bg-white');

    $avatarSize = $isTopic ? 'h-9 w-9 text-xs' : 'h-8 w-8 text-[11px]';
@endphp

<div {{ $attributes->class($rootClasses) }}>
    <article class="{{ $articleClasses }}">
        <div class="{{ $isTopic ? 'flex flex-col gap-2 text-[11px] text-slate-500 sm:flex-row sm:items-start sm:justify-between sm:gap-3' : 'mb-2 flex flex-col gap-2 text-[11px] text-slate-500 sm:flex-row sm:items-start sm:justify-between sm:gap-3' }}">
            <x-chat.user-identity :name="$primaryLabel" :secondary="$secondaryLabel"
                :avatar="$avatarUrl !== '' ? $avatarUrl : null" :badgeIcon="$badgeIcon" :badgeColor="$badgeColor"
                :avatarAlt="$senderFullName !== '' ? $senderFullName : $primaryLabel" :avatarSizeClass="$avatarSize"
                wrapperClass="flex min-w-0 flex-1 items-start gap-2" textWrapperClass="min-w-0"
                nameClass="truncate text-sm font-semibold text-slate-800" secondaryClass="truncate text-xs text-slate-500" />

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

        <div class="mt-1.5 text-sm leading-relaxed text-slate-800 wrap-break-word sm:mt-2">
            @if ($isDeleted)
                <span class="text-slate-500 italic">
                    {{ $isTopic ? ($isMine ? 'თქვენ წაშალაეთ ეს მესიჯი.' : 'ეს მესიჯი წაშლილია ავტორის მიერ.') : ($isMine ? 'თქვენ წაშალეთ ეს მესიჯი.' : 'ეს მესიჯი წაშლილია.') }}
                </span>
            @elseif ($isEditing)
                <div class="space-y-2">
                    <textarea wire:model.defer="editContent" rows="3"
                        class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm transition"></textarea>

                    @if ((int) $editingMessageId === (int) ($message['id'] ?? 0))
                        @php
                            $editError = $errors->first('editContent');
                        @endphp
                        @if ($editError)
                            <p class="text-xs text-rose-600">{{ $editError }}</p>
                        @endif
                    @endif

                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="cancelEditMessage"
                            class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
                            გაუქმება
                        </button>
                        <button type="button" wire:click="saveEditedMessage" wire:loading.attr="disabled"
                            wire:target="saveEditedMessage"
                            class="inline-flex items-center rounded-md border border-primary-200 bg-primary-50 px-3 py-1.5 text-xs font-semibold text-primary-700 transition hover:border-primary-300 hover:bg-primary-100">
                            შენახვა
                        </button>
                    </div>
                </div>
            @else
                {{ $message['content'] ?? '' }}
            @endif
        </div>

        @if (!$isDeleted && !empty($message['attachments']))
            @php
                $attachments = collect($message['attachments'] ?? []);
                $imageAttachments = $attachments
                    ->filter(fn($a) => ($a['type'] ?? '') === 'image' || str_starts_with(($a['mime_type'] ?? ''), 'image/'))
                    ->values()->all();
                $docAttachments = $attachments
                    ->filter(fn($a) => !(($a['type'] ?? '') === 'image' || str_starts_with(($a['mime_type'] ?? ''), 'image/')))
                    ->values()->all();
            @endphp

            @if (!empty($docAttachments))
                <ul class="mt-2 space-y-1">
                    @foreach ($docAttachments as $attachment)
                        @php($attachmentUrl = $attachment['download_url'] ?? $attachment['url'])
                        <li class="text-xs text-slate-600">
                            <a class="underline decoration-slate-300 underline-offset-2 hover:text-slate-900"
                                href="{{ $attachmentUrl }}" target="_blank" rel="noopener">
                                {{ $attachment['original_name'] ?? 'attachment' }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if (!empty($imageAttachments))
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($imageAttachments as $attachment)
                        @php($attachmentUrl = $attachment['download_url'] ?? $attachment['url'])

                        @if ($isTopic)
                            <div class="relative size-24 sm:size-32">
                                <a href="{{ $attachmentUrl }}" download title="სურათის ჩამოტვირთვა"
                                    class="absolute right-1 bottom-1 z-10 rounded-full bg-white/95 px-1.5 py-1 text-[10px] font-semibold text-slate-700 shadow-sm ring-1 ring-black/5 hover:bg-white sm:right-2 sm:bottom-2 sm:px-2">
                                    <x-app-icon name="cloud-arrow-down" class="size-3" />
                                </a>

                                <a href="{{ $attachmentUrl }}"
                                    class="group block aspect-square overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                                    <img src="{{ $attachmentUrl }}" alt="{{ $attachment['original_name'] ?? 'image' }}"
                                        class="h-full w-full object-cover transition group-hover:scale-[1.02]" loading="lazy" />
                                </a>
                            </div>
                        @else
                            <div class="relative size-20 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 sm:size-24">
                                <a href="{{ $attachmentUrl }}" download title="სურათის ჩამოტვირთვა"
                                    class="absolute right-0 bottom-0 z-10 rounded-full bg-white/95 px-1.5 py-1 text-[10px] font-semibold text-slate-700 shadow-sm ring-1 ring-black/5 hover:bg-white">
                                    <x-app-icon name="cloud-arrow-down" class="size-3" />
                                </a>

                                <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener" class="group block h-full w-full">
                                    <img src="{{ $attachmentUrl }}" alt="{{ $attachment['original_name'] ?? 'image' }}"
                                        class="h-full w-full object-cover transition group-hover:scale-[1.02]" loading="lazy" />
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        @endif

        @if (!$isDeleted)
            <div class="{{ $isTopic ? 'mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500' : 'mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500' }}">
                @if ($isTopic)
                    @if ($currentUserId)
                        <button type="button" wire:click="toggleLike({{ $message['id'] }})" wire:loading.attr="disabled"
                            wire:target="toggleLike({{ $message['id'] }})"
                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-sm font-medium ring-1 transition-colors {{ $likedByMe
                                ? 'ring-primary-300 bg-primary-50 text-primary-700'
                                : 'ring-slate-200 text-slate-700 hover:bg-slate-50 hover:text-slate-700 hover:ring-slate-300' }}">
                            <x-app-icon name="hand-thumb-up" variant="{{ $likedByMe ? 's' : 'o' }}"
                                class="size-3 opacity-80" />
                            <span class="tabular-nums text-xs">{{ $likeCount }}</span>
                            <span class="sr-only">Like</span>
                        </button>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-slate-500 ring-1 ring-slate-200">
                            <x-app-icon name="hand-thumb-up" variant="o" class="size-3" />
                            <span class="tabular-nums">{{ $likeCount }}</span>
                        </span>
                    @endif

                    @if ($canEdit && !$isEditing)
                        <button type="button" wire:click="startEditMessage({{ $message['id'] }})"
                            wire:loading.attr="disabled" wire:target="startEditMessage({{ $message['id'] }})"
                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-slate-600 ring-1 ring-slate-200 transition hover:bg-slate-50 hover:text-slate-800 hover:ring-slate-300">
                            <x-app-icon name="pencil-square" class="size-4!" />
                            <span>ჩასწორება</span>
                        </button>
                    @endif
                @else
                    <button type="button" wire:click="toggleLike({{ $message['id'] }})" wire:loading.attr="disabled"
                        wire:target="toggleLike({{ $message['id'] }})"
                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 font-semibold ring-1 transition {{ $likedByMe ? 'ring-primary-200 text-primary-600 bg-primary-50/50' : 'ring-slate-200 text-slate-600 hover:ring-slate-300 hover:text-slate-900' }}">
                        <x-app-icon name="hand-thumb-up" variant="{{ $likedByMe ? 's' : 'o' }}" class="size-5!" />
                        <span class="tabular-nums">{{ $likeCount }}</span>
                        <span class="sr-only">Like</span>
                    </button>

                    @if ($isMine)
                        <button type="button" wire:click="deleteMessage({{ $message['id'] }})" wire:loading.attr="disabled"
                            wire:target="deleteMessage({{ $message['id'] }})"
                            class="rounded-full px-2.5 py-1 text-red-600 ring-1 ring-transparent transition hover:ring-red-200 hover:bg-red-50">
                            <x-app-icon name="trash" class="size-4.5!" />
                            <span class="sr-only">Delete</span>
                        </button>
                    @endif
                @endif
            </div>
        @endif
    </article>
</div>
