@props([
    'type' => 'button',
    'variant' => 'primary', // primary | secondary | ghost
    'size' => 'md', // sm | md | lg
    'iconPosition' => 'left', // left | right
    'numeric' => false,
    'disabled' => false,

    'textClass' => '',
])

@php
    $hasIcon = isset($icon) && $icon instanceof Illuminate\View\ComponentSlot && $icon->isNotEmpty();
    $hasText = $slot->isNotEmpty();
    $iconOnly = $hasIcon && ! $hasText;

    $base = 'inline-flex items-center justify-center gap-2 cursor-pointer rounded-md font-medium transition focus:outline-none';

    $sizes = [
        'sm' => 'text-xs px-2.5 py-1.5',
        'md' => 'text-sm px-3 py-2',
        'lg' => 'text-base px-4 py-2.5',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];

    $variants = [
        'primary' => 'bg-primary-500 text-white hover:bg-primary-600/90 focus:ring-2 focus:ring-offset-2 focus:ring-gray-900/20',
        'secondary' => 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-offset-2 focus:ring-gray-400/20',
        'ghost' => 'text-gray-700 hover:text-gray-900 ring-0 border-0 focus:ring-0 focus:ring-offset-0',
    ];
    $variantClass = $variants[$variant] ?? $variants['primary'];

    $numericClass = $numeric ? 'tabular-nums min-w-[2.5rem] justify-center' : '';
    $iconOnlyClass = $iconOnly ? 'p-2' : $sizeClass;
    $disabledClass = $disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : '';

    $buttonAttributes = $attributes
        ->class([
            $base,
            $variantClass,
            $iconOnlyClass,
            $numericClass,
            $disabledClass,
        ])
        ->merge([
            'type' => $type,
            'aria-disabled' => $disabled ? 'true' : 'false',
        ]);
@endphp

<button {{ $buttonAttributes }} @disabled($disabled)>
    @if($hasIcon && $iconPosition === 'left')
        <span class="shrink-0">
            {{ $icon }}
        </span>
    @endif

    @if($hasText)
        <span class="whitespace-nowrap {{ $textClass }}">{{ $slot }}</span>
    @endif

    @if($hasIcon && $iconPosition === 'right')
        <span class="shrink-0">
            {{ $icon }}
        </span>
    @endif
</button>
