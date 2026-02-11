@props([
    'user',
    'size' => 'sm',
])

@php
    $menuId = 'user-menu-' . uniqid();
@endphp

<div class="relative" x-data="{ open: false, wasOpen: false }" x-effect="
        if (open && !wasOpen) { $nextTick(() => $refs.firstItem?.focus()) }
        if (!open && wasOpen) { $nextTick(() => $refs.trigger?.focus()) }
        wasOpen = open
    " @keydown.escape.window="if (open) open = false">
    <button type="button" x-ref="trigger" @click="open = !open"
        class="inline-flex items-center rounded-full px-1 py-0.5 transition hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-primary-300"
        aria-haspopup="menu" aria-controls="{{ $menuId }}" x-bind:aria-expanded="open ? 'true' : 'false'">
        <x-ui.avatar :user="$user" :size="$size" badgeX="left" />
        <x-app-icon name="chevron-down" variant="m" class="ml-0.5 transition duration-200"
            x-bind:class="open ? 'rotate-180 text-slate-900' : 'text-slate-500'" />
    </button>

    <div id="{{ $menuId }}" role="menu" x-cloak x-show="open" x-transition.origin.top.right.duration.150ms
        @click.outside="open = false"
        class="absolute right-0 z-50 mt-2 w-52 rounded-xl border border-slate-200/70 bg-white p-1 text-sm shadow-lg shadow-slate-900/5">
        <a href="{{ route('page.profile') }}" x-ref="firstItem" @click="open = false"
            class="flex items-center gap-2 rounded-lg px-3 py-2 text-slate-700 transition hover:bg-slate-100/80 hover:text-slate-900">
            ჩემი პროფილი
        </a>

        <div class="my-1 h-px bg-slate-200/70"></div>

        <form method="POST" action="{{ route('auth.logout') }}" @submit="open = false">
            @csrf
            <x-button type="submit" variant="ghost" class="w-full justify-start gap-2 rounded-lg px-3 py-2 text-slate-700 transition hover:bg-slate-100/80 hover:text-slate-900
                 focus-visible:ring-2 focus-visible:ring-slate-300" data-no_loading>
                გასვლა
            </x-button>
        </form>
    </div>
</div>
