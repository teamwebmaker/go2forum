@props([
    'id',
    'title' => null,
    'size' => 'md',             // sm | md | lg | xl | 2xl | full
    'closeOnOutside' => true,
    'closeOnEsc' => true,
])

@php
    $sizes = [
        'sm'   => 'max-w-sm',
        'md'   => 'max-w-md',
        'lg'   => 'max-w-lg',
        'xl'   => 'max-w-xl',
        '2xl'  => 'max-w-2xl',
        'full' => 'w-full h-full max-w-none',
    ];
    $panelSize = $sizes[$size] ?? $sizes['md'];
@endphp

<div
    id="{{ $id }}"
    class="ui-modal fixed inset-0 z-50 hidden"
    data-modal
    data-close-outside="{{ $closeOnOutside ? 'true' : 'false' }}"
    data-close-esc="{{ $closeOnEsc ? 'true' : 'false' }}"
    aria-hidden="true"
>
    <!-- Backdrop (starts invisible for animation) -->
    <div class="ui-modal-backdrop absolute inset-0 bg-black/50 opacity-0 transition-opacity duration-200"></div>

    <!-- Wrapper -->
    <div class="relative min-h-full flex items-center justify-center p-4">
        <!-- Panel (starts invisible for animation) -->
        <div
            class="ui-modal-panel relative w-full {{ $panelSize }} bg-white rounded-lg shadow-lg
                   opacity-0 scale-95 translate-y-2
                   transition-all duration-200 ease-out"
            role="dialog"
            aria-modal="true"
            @if($title) aria-labelledby="{{ $id }}-title" @endif
        >
            <!-- Header -->
            <div class="flex items-start justify-between gap-4 p-4 pb-2 border-b border-gray-200">
                <div class="min-w-0">
                    @if($title)
                        <h2 id="{{ $id }}-title" class="text-lg font-semibold truncate">
                            {{ $title }}
                        </h2>
                    @endif
                </div>

                <button
                    type="button"
                    class="ui-modal-close shrink-0 cursor-pointer"
                    aria-label="Close modal"
                    data-modal-close
                >
                    <x-app-icon name="x-mark" variant="m"/>
                </button>

            </div>

            <!-- Body -->
            <div class="p-4">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @isset($footer)
                <div class="p-4 border-t border-gray-200">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
