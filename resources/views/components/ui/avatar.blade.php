@props([
    'user' => null,
    'size' => 'md', // xs|sm|md|lg|xl
    'showBadges' => true,
    'badgeY' => 'bottom', // top|bottom
    'badgeX' => 'right', // left|right
])

@php
    use App\Support\BadgeColors;
    
    $initials = $user?->initials ?? '?';
    $avatarUrl = $user?->avatar_url;

    $badgeColor = BadgeColors::forUser($user);

    $sizes = [
        'xs' => ['container' => 'h-8 w-8 text-xs',  'icon' => 'size-4!',],
        'sm' => ['container' => 'h-9 w-9 text-sm',  'icon' => 'size-4!',],
        'md' => ['container' => 'h-12 w-12 text-base','icon' => 'size-5!',],
        'lg' => ['container' => 'h-16 w-16 text-xl', 'icon' => 'size-6!',],
        'xl' => ['container' => 'h-20 w-20 text-2xl','icon' => 'size-7!',],
    ];

    $posY = [
        'top' => '-top-0.5',
        'bottom' => '-bottom-0.5',
    ][$badgeY] ?? '-bottom-0.5';

    $posX = [
        'left' => '-left-0.5',
        'right' => '-right-0.5',
    ][$badgeX] ?? '-right-0.5';

    $position = "absolute {$posY} {$posX}";
    $currentSize = $sizes[$size] ?? $sizes['md'];
@endphp

<span {{ $attributes->class(['relative inline-flex', $currentSize['container']]) }}>
    <span class="inline-flex w-full h-full items-center justify-center rounded-full overflow-hidden border border-slate-200 text-slate-700 font-semibold">
        @if ($avatarUrl)
            <img
                src="{{ $avatarUrl }}"
                alt="{{ $user?->name ?? 'User avatar' }}"
                class="h-full w-full object-cover"
                loading="lazy"
            />
        @else
            {{ $initials }}
        @endif
    </span>

@if ($showBadges)
    @if ($badgeColor)
        <x-ui.avatar-badge
            iconClass="{{ $badgeColor }}"
            iconSizeClass="{{ $currentSize['icon'] }}"
            wrapperClass="{{ $position }}"
        />
    @endif
@endif

</span>
