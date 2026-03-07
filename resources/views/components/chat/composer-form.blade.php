@props([
    'placeholder' => 'შეტყობინება...',
    'disabled' => false,
    'formClass' => 'space-y-2',
    'bodyClass' => '',
    'inputColumnClass' => '',
    'uploadColumnClass' => '',
    'textareaClass' => 'w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2 pb-10 pr-10 text-sm text-slate-900 shadow-sm',
    'toggleButtonClass' => 'absolute bottom-3 left-2 inline-flex size-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:text-slate-900',
    'toggleAriaLabel' => 'დანართების დამატება',
    'contentErrorClass' => 'text-xs text-rose-600',
    'sendInline' => false,
    'sendWrapperClass' => 'flex justify-end',
    'sendButtonVariant' => null,
    'sendButtonSize' => null,
    'sendButtonClass' => '',
    'sendDisabled' => false,
    'sendLabel' => 'გაგზავნა',
    'sendingLabel' => 'იგზავნება...',
    'wireTarget' => 'sendMessage, attachments',
    'uploadOuterClass' => 'max-w-sm',
    'uploadInnerClass' => '',
    'uploadKey' => null,
    'uploadHelpText' => null,
    'maxUploadSize' => null,
    'replyContext' => null,
    'cancelReplyAction' => null,
])

@php
    $resolvedMaxUploadSize = $maxUploadSize ?? (int) config('chat.attachments_max_kb', 20480);
    $resolvedSendButtonVariant = $sendButtonVariant ?: 'primary';
    $resolvedSendButtonSize = $sendButtonSize ?: 'md';
@endphp

<form wire:submit.prevent="sendMessage" class="{{ $formClass }}" x-data="{ showUploads: $wire.entangle('showUploads') }">
    <div @class([$bodyClass])>
        <div @class([$inputColumnClass])>
            @if (is_array($replyContext))
                <div class="mb-2 flex items-start justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold text-slate-700">
                            პასუხი: {{ $replyContext['author'] ?? 'მომხმარებელი' }}
                        </p>
                        <p class="truncate text-xs text-slate-600">
                            {{ $replyContext['content'] ?? '' }}
                        </p>
                    </div>

                    @if ($cancelReplyAction)
                        <button type="button" wire:click="{{ $cancelReplyAction }}"
                            class="inline-flex size-6 shrink-0 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-200 hover:text-slate-700"
                            aria-label="Cancel reply">
                            <x-app-icon name="x-mark" class="size-4" />
                        </button>
                    @endif
                </div>
            @endif

            <div class="relative">
                <textarea wire:model.defer="content" rows="3" placeholder="{{ $placeholder }}"
                    class="{{ $textareaClass }}" @disabled($disabled)></textarea>
                <button type="button" class="{{ $toggleButtonClass }}" aria-label="{{ $toggleAriaLabel }}"
                    @click="showUploads = !showUploads" @disabled($disabled)>
                    <span x-show="!showUploads">
                        <x-app-icon name="plus" class="size-4" />
                    </span>
                    <span x-show="showUploads">
                        <x-app-icon name="plus" class="size-4 rotate-45" />
                    </span>
                </button>
            </div>

            @error('content')
                <p class="{{ $contentErrorClass }}">{{ $message }}</p>
            @enderror

            @if ($sendInline)
                <div class="{{ $sendWrapperClass }}">
                    <x-button type="submit" wire:loading.attr="disabled" wire:target="{{ $wireTarget }}"
                        :disabled="$sendDisabled" :variant="$resolvedSendButtonVariant"
                        :size="$resolvedSendButtonSize"
                        class="{{ $sendButtonClass }}">
                        <span wire:loading.remove wire:target="{{ $wireTarget }}">
                            {{ $sendLabel }}
                        </span>
                        <span wire:loading wire:target="{{ $wireTarget }}">
                            {{ $sendingLabel }}
                        </span>
                    </x-button>
                </div>
            @endif
        </div>

        <div @class([$uploadColumnClass])>
            <div @class([$uploadOuterClass])>
                <div x-show="showUploads" x-collapse @class([$uploadInnerClass])>
                    @if ($uploadHelpText !== null)
                        <livewire:upload-field wire:model="attachments" label="" :multiple="true" :key="$uploadKey"
                            :max-size="$resolvedMaxUploadSize" :help-text="$uploadHelpText" />
                    @else
                        <livewire:upload-field wire:model="attachments" label="" :multiple="true" :key="$uploadKey"
                            :max-size="$resolvedMaxUploadSize" />
                    @endif
                </div>
            </div>
        </div>
    </div>

    @unless ($sendInline)
        <div class="{{ $sendWrapperClass }}">
            <x-button type="submit" wire:loading.attr="disabled" wire:target="{{ $wireTarget }}"
                :disabled="$sendDisabled" :variant="$resolvedSendButtonVariant"
                :size="$resolvedSendButtonSize"
                class="{{ $sendButtonClass }}">
                <span wire:loading.remove wire:target="{{ $wireTarget }}">
                    {{ $sendLabel }}
                </span>
                <span wire:loading wire:target="{{ $wireTarget }}">
                    {{ $sendingLabel }}
                </span>
            </x-button>
        </div>
    @endunless
</form>
