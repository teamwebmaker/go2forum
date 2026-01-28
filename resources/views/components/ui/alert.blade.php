@props([
    'type' => 'info',   // success | error | warning | info
    'closable' => false,
    'slotClasses' => '',
])

@php
    $styles = [
        'success' => [
            'bg' => 'bg-green-50',
            'text' => 'text-green-800',
            'border' => 'border-green-200',
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'text' => 'text-red-800',
            'border' => 'border-red-200',
        ],
        'warning' => [
            'bg' => 'bg-amber-50',
            'text' => 'text-amber-800',
            'border' => 'border-amber-200',
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'text' => 'text-blue-800',
            'border' => 'border-blue-200',
        ],
    ];

    $style = $styles[$type] ?? $styles['info'];
@endphp

<div
    class="alert-component relative z-40 w-full border px-4 {{ $style['bg'] }} {{ $style['border'] }}"
    data-closable="{{ $closable ? 'true' : 'false' }}"
>
    <div
        class="relative mx-auto flex max-w-6xl items-start justify-between gap-4 rounded-xl px-5 py-3 text-sm font-medium {{ $style['text'] }}"
    >
        <div class="flex flex-wrap items-center gap-3 {{ $slotClasses }}">
            {{ $slot }}
        </div>

        @if ($closable)
            <button
                type="button"
                class="alert-close shrink-0 text-sm font-semibold opacity-70 hover:opacity-100 focus:outline-none"
                aria-label="Close alert"
            >
                ✕
            </button>
        @endif
    </div>
</div>
