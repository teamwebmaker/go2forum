@if (!$isDeleted)
    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
        @if ($isTopic)
            @if ($currentUserId)
                <div class="flex items-center gap-2">
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

                    @if ($canReply)
                        <button type="button" wire:click="setReplyToMessage({{ $message['id'] }})"
                            wire:loading.attr="disabled" wire:target="setReplyToMessage({{ $message['id'] }})"
                            class="inline-flex items-center rounded-full px-2.5 py-1 text-slate-600 ring-1 ring-slate-200 transition hover:bg-slate-50 hover:text-slate-800 hover:ring-slate-300">
                            პასუხი
                        </button>
                    @endif
                </div>
            @else
                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-slate-500 ring-1 ring-slate-200">
                    <x-app-icon name="hand-thumb-up" variant="o" class="size-3" />
                    <span class="tabular-nums">{{ $likeCount }}</span>
                </span>
            @endif

            @if ($canEdit && !$isEditing)
                <button type="button" wire:click="startEditMessage({{ $message['id'] }})" wire:loading.attr="disabled"
                    wire:target="startEditMessage({{ $message['id'] }})"
                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-slate-600 ring-1 ring-slate-200 transition hover:bg-slate-50 hover:text-slate-800 hover:ring-slate-300">
                    <x-app-icon name="pencil-square" class="size-4!" />
                    <span>ჩასწორება</span>
                </button>
            @endif
        @else
            <div class="flex items-center gap-2">
                <button type="button" wire:click="toggleLike({{ $message['id'] }})" wire:loading.attr="disabled"
                    wire:target="toggleLike({{ $message['id'] }})"
                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 font-semibold ring-1 transition {{ $likedByMe ? 'ring-primary-200 text-primary-600 bg-primary-50/50' : 'ring-slate-200 text-slate-600 hover:ring-slate-300 hover:text-slate-900' }}">
                    <x-app-icon name="hand-thumb-up" variant="{{ $likedByMe ? 's' : 'o' }}" class="size-5!" />
                    <span class="tabular-nums">{{ $likeCount }}</span>
                    <span class="sr-only">Like</span>
                </button>

                @if ($currentUserId && $canReply)
                    <button type="button" wire:click="setReplyToMessage({{ $message['id'] }})"
                        wire:loading.attr="disabled" wire:target="setReplyToMessage({{ $message['id'] }})"
                        class="inline-flex items-center rounded-full px-2.5 py-1 text-slate-600 ring-1 ring-slate-200 transition hover:bg-slate-50 hover:text-slate-800 hover:ring-slate-300">
                        პასუხი
                    </button>
                @endif
            </div>

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
