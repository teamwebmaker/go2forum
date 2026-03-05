@props([
    'containerClass' => 'relative bg-slate-50 px-4 py-3',
    'listClass' => 'h-[40dvh] space-y-3 overflow-y-auto overscroll-contain pr-1',
    'loadingTarget' => 'loadOlder',
    'loadingLabel' => 'იტვირთება...',
    'goDownTarget' => 'loadLatest',
    'goDownAriaLabel' => 'Go down',
    'goDownWrapperClass' => 'bottom-8',
    'goDownButtonClass' => 'pointer-events-auto rounded-full! border border-slate-200 bg-white/90 text-slate-700 shadow-sm transition duration-200 ease-out hover:bg-white opacity-0 translate-y-2',
])

<div {{ $attributes->class($containerClass) }}>
    <div class="{{ $listClass }}" data-chat-list>
        <div wire:loading.flex wire:target="{{ $loadingTarget }}" class="sticky top-0 z-10 -mt-1 mb-2 items-center justify-center">
            <div
                class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-[11px] text-slate-600 ring-1 ring-slate-200">
                <span
                    class="inline-block size-3 animate-spin rounded-full border-2 border-slate-300 border-t-slate-600"></span>
                {{ $loadingLabel }}
            </div>
        </div>

        {{ $slot }}
    </div>

    <div class="pointer-events-none absolute left-1/2 z-10 -translate-x-1/2 {{ $goDownWrapperClass }}">
        <x-button type="button" variant="secondary" size="sm" wire:click="{{ $goDownTarget }}"
            class="{{ $goDownButtonClass }}" aria-label="{{ $goDownAriaLabel }}" data-chat-go-down>
            <x-slot:icon>
                <x-app-icon name="chevron-down" class="size-4" />
            </x-slot:icon>
        </x-button>
    </div>
</div>
