@props([
    'type' => 'info',   // success | error | warning | info
    'closable' => false,
    'slotClasses' => '',
])

@php
    $colors = [
        'success' => 'bg-green-100 text-green-800 border-green-300',
        'error'   => 'bg-red-100 text-red-800 border-red-300',
        'warning' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
        'info'    => 'bg-blue-100 text-blue-800 border-blue-300',
    ];

    $classes = $colors[$type] ?? $colors['info'];
@endphp

<div
    class="alert-component w-full border-y {{ $classes }}"
    data-closable="{{ $closable ? 'true' : 'false' }}"
>
    <div class="max-w-6xl mx-auto px-6 py-3 flex items-start justify-between gap-4">
        <!-- Content -->
        <div class="flex flex-wrap items-center gap-3 text-sm font-medium {{ $slotClasses }}">
            {{ $slot }}
        </div>

        <!-- Close button -->
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
