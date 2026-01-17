@props([
    'type' => 'info', // success | error | warning | info
    'timeout' => 5000,
    'messages' => [],
])

@php
    $timeout = $type === 'error' ? 6000 : $timeout;
    $messages = is_array($messages) ? $messages : (filled($messages) ? [$messages] : []);

    $variants = [
        'success' => [
            'icon'     => 'text-emerald-600',
            'accent'   => 'bg-emerald-500',
            'subtext'  => 'text-slate-600',
        ],
        'error' => [
            'icon'     => 'text-rose-600',
            'accent'   => 'bg-rose-500',
            'subtext'  => 'text-slate-600',
        ],
        'warning' => [
            'icon'     => 'text-amber-600',
            'accent'   => 'bg-amber-500',
            'subtext'  => 'text-slate-600',
        ],
        'info' => [
            'icon'     => 'text-sky-600',
            'accent'   => 'bg-sky-500',
            'subtext'  => 'text-slate-600',
        ],
    ];

    $icons = [
        'success' => '<svg fill="none" stroke-width="1.8" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
        'warning' => '<svg fill="none" stroke-width="1.8" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.008v.008H12v-.008Z"/></svg>',
        'error'   => '<svg fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>',
        'info'    => '<svg fill="none" stroke-width="1.8" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>',
    ];

    $variant = $variants[$type] ?? $variants['info'];
    $icon = $icons[$type] ?? $icons['info'];

    $baseClasses = 'js-toast group relative w-fit max-w-[min(28rem,calc(100vw-2rem))]
        overflow-hidden rounded-xl bg-white shadow-lg
        ring-1 ring-black/5
        transform-gpu transition duration-200 ease-out
        opacity-0 -translate-y-1 scale-[0.98] pointer-events-none';
@endphp

@foreach ($messages as $message)
    <div
        data-toast
        data-timeout="{{ $timeout }}"
        data-toast-hidden="1"
        {{ $attributes->merge(['class' => $baseClasses]) }}
        role="alert"
        aria-live="polite"
    >
        <div class="flex items-center gap-3 py-3 pl-4 pr-10">
            @if (array_key_exists($type, $variants))
                <span class="inline-flex h-6 w-6 shrink-0 {{ $variant['icon'] }}">
                    <span class="[&>svg]:h-6 [&>svg]:w-6">{!! $icon !!}</span>
                </span>
            @endif

            <div class="min-w-0 flex-1">
                <div class="mt-0.5 space-y-0.5 text-sm {{ $variant['subtext'] }}">
                    <p class="whitespace-normal wrap-break-word">{{ $message }}</p>
                </div>
            </div>
        </div>

        <x-button class="js-toast-close absolute right-2 top-1/2 -translate-y-1/2 p-1!" variant="ghost">
            <x-app-icon name="x-mark" variant="m" />
        </x-button>
    </div>
@endforeach
