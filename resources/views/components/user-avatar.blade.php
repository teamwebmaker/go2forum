@props(['user'])

@php
    $name = $user?->name ?? '';
    $initial = $user?->initials ?? '';
    $avatarUrl = $user?->avatar_url ?? null;
@endphp

<details class="relative">
    <summary class="list-none cursor-pointer flex items-center">
        <span
            class="inline-flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-slate-200 text-sm font-semibold text-slate-700">
            @if ($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="{{ $name }}" class="h-full w-full object-cover" />
            @else
                {{ $initial }}
            @endif
        </span>
        <x-app-icon name="chevron-down" variant="m" class="ml-0.5" />
    </summary>

    <div class="absolute right-0 mt-2 w-48 rounded-xl border border-slate-200/70
         bg-white p-1 text-sm shadow-lg shadow-slate-900/5">
        <a href="{{ route('page.profile') }}" class="flex items-center gap-2 rounded-lg px-3 py-2
             text-slate-700 transition
             hover:bg-slate-100/80 hover:text-slate-900">
            ჩემი პროფილი
        </a>

        <div class="my-1 h-px bg-slate-200/70"></div>

        <form method="POST" action="{{ route('auth.logout') }}">
            @csrf
            <x-button type="submit" variant="ghost" class="w-full justify-start gap-2 rounded-lg px-3 py-2
                 text-slate-700 transition
                 hover:bg-slate-100/80 hover:text-slate-900
                 focus-visible:ring-2 focus-visible:ring-slate-300" data-no_loading>
                გასვლა
            </x-button>
        </form>
    </div>

</details>