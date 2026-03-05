@props([
    'name' => 'User',
    'secondary' => null,
    'avatar' => null,
    'badgeIcon' => null,
    'badgeColor' => '',
    'showBadge' => true,
    'badgePlacement' => 'overlay', // overlay|inline
    'showAvatar' => true,
    'showFallbackAvatar' => true,
    'avatarAlt' => null,
    'avatarSizeClass' => 'h-9 w-9 text-xs',
    'avatarImageClass' => 'rounded-full object-cover ring-1 ring-slate-200',
    'avatarFallbackClass' => 'rounded-full border border-slate-200 bg-slate-100 font-semibold text-slate-700',
    'badgeSizeClass' => 'size-4!',
    'wrapperClass' => 'flex min-w-0 items-start gap-2',
    'textWrapperClass' => 'min-w-0',
    'nameClass' => 'truncate text-sm font-semibold text-slate-800',
    'secondaryClass' => 'truncate text-xs text-slate-500',
])

@php
    $displayName = trim((string) $name);
    if ($displayName === '') {
        $displayName = 'User';
    }

    $displaySecondary = is_string($secondary) ? trim($secondary) : $secondary;
    $displaySecondary = filled($displaySecondary) ? $displaySecondary : null;

    $avatarUrl = is_string($avatar) ? trim($avatar) : '';
    $avatarUrl = $avatarUrl !== '' ? $avatarUrl : null;
    $renderAvatar = $showAvatar && ($avatarUrl || $showFallbackAvatar);

    $avatarLabel = trim((string) ($avatarAlt ?? $displayName)) ?: $displayName;

    $initials = collect(preg_split('/\s+/u', $displayName) ?: [])
        ->filter()
        ->take(2)
        ->map(fn($part) => mb_substr((string) $part, 0, 1))
        ->implode('');
    $initials = $initials !== '' ? mb_strtoupper($initials) : 'U';
@endphp

<div class="{{ $wrapperClass }}">
    @if ($renderAvatar)
        <div class="relative shrink-0">
            @if ($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="{{ $avatarLabel }}"
                    class="{{ $avatarSizeClass }} {{ $avatarImageClass }}" loading="lazy" />
            @else
                <div class="inline-flex {{ $avatarSizeClass }} items-center justify-center {{ $avatarFallbackClass }}">
                    {{ $initials }}
                </div>
            @endif

            @if ($showBadge && $badgePlacement === 'overlay' && !empty($badgeIcon))
                <x-ui.avatar-badge iconName="{{ $badgeIcon }}" iconClass="{{ $badgeColor }}"
                    iconSizeClass="{{ $badgeSizeClass }}" wrapperClass="absolute -bottom-0.5 -right-0.5" />
            @endif
        </div>
    @endif

    <div class="{{ $textWrapperClass }}">
        <div class="flex min-w-0 items-center gap-1">
            @if ($showBadge && $badgePlacement === 'inline' && !empty($badgeIcon))
                <x-ui.avatar-badge iconName="{{ $badgeIcon }}" iconClass="{{ $badgeColor }}"
                    iconSizeClass="{{ $badgeSizeClass }}" wrapperClass="inline-flex shrink-0" badgeClass="inline-flex" />
            @endif
            <p class="{{ $nameClass }}">{{ $displayName }}</p>
        </div>
        @if ($displaySecondary)
            <p class="{{ $secondaryClass }}">{{ $displaySecondary }}</p>
        @endif
    </div>
</div>
