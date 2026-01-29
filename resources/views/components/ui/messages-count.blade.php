@props([
    'count' => 0,
    'icon' => 'chat-bubble-left-ellipsis',
    'variant' => 's',
])

@php
    $count = (int) $count;

    if ($count < 100) {
        $display = (string) $count;
    } elseif ($count < 1000) {
        $display = (string) (intdiv($count, 100) * 100) . '+';
    } else {
        $display = '999+';
    }
@endphp

<div {{ $attributes->class('relative shrink-0 rounded-full p-2 text-primary-600') }}>
    <x-app-icon :name="$icon" :variant="$variant" class="size-6!" />
    <span
        class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[11px] font-bold leading-none text-white">
        {{ $display }}
    </span>
</div>
