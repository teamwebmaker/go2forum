<header class="border-b border-slate-200 bg-white">
    @php
        $linkClass = fn (bool $active) => 'transition ' . ($active ? 'text-slate-700 font-semibold' : 'text-gray-700 hover:text-slate-900');

        $guestLinks = [
            ['label' => 'სახლი', 'route' => 'page.home'],
            ['label' => 'შესვლა', 'route' => 'login'],
            ['label' => 'რეგისტრაცია', 'route' => 'register'],
        ];

        $authLinks = [
            ['label' => 'სახლი', 'route' => 'page.home'],
            ['label' => 'პროფილი', 'route' => 'profile.user-info'],
            ['label' => 'ვერიფიკაცია', 'route' => 'profile.verification'],
        ];
    @endphp
    <nav class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4">
        <a class="text-lg font-semibold tracking-tight text-slate-900" href="/">
            {{ config('app.name', default: 'go2forum') }}
        </a>
        <div class="flex items-center gap-6 text-sm font-medium text-slate-600">
            @if (!Auth::user())
                @foreach ($guestLinks as $link)
                    <a class="{{ $linkClass(request()->routeIs($link['route'])) }}" href="{{ route($link['route']) }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            @else
                @foreach ($authLinks as $link)
                    <a class="{{ $linkClass(request()->routeIs($link['route'])) }}" href="{{ route($link['route']) }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
                <x-user-avatar :user="Auth::user()" />
            @endif
        </div>
    </nav>
</header>
