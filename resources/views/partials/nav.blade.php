<header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white shadow-xs">
    @php
        $isAuth = (bool) Auth::user();
        $baseLink =
            'relative inline-flex items-center rounded-md px-2 py-1 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2';

        $linkClass = fn(bool $active) => $baseLink .
            ' ' .
            ($active ? 'text-slate-950' : 'text-slate-700 hover:text-slate-950');

        // Optional: slightly more emphasis on Register without being "too much"
        $ctaClass =
            'inline-flex items-center rounded-md px-3 py-1.5 text-sm font-semibold shadow-sm bg-primary-500 text-white hover:bg-primary-600/90 focus:ring-2 focus:ring-offset-2 focus:ring-gray-900/20';

        $guestLinks = [
            ['label' => 'სახლი', 'route' => 'page.home'],
            ['label' => 'შესვლა', 'route' => 'login'],
            ['label' => 'რეგისტრაცია', 'route' => 'register', 'cta' => true],
        ];

        $authLinks = [['label' => 'სახლი', 'route' => 'page.home']];
    @endphp

    <nav class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-3">
        <a class="inline-flex items-center gap-2 rounded-md px-2 py-1 text-xl font-semibold tracking-tight text-slate-900 transition hover:text-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
            href="/" aria-label="{{ config('app.name', default: 'go2forum') }}">
            <span class="select-none">{{ config('app.name', default: 'go2forum') }}</span>
        </a>

        <div class="flex gap-2 align-center">

            <div class="hidden items-center gap-2 text-sm font-medium xs:flex">
                @foreach ($isAuth ? $authLinks : $guestLinks as $link)
                    @php $active = request()->routeIs($link['route']); @endphp

                    @if (!empty($link['cta']) && !$isAuth)
                        <a class="{{ $ctaClass }}" href="{{ route($link['route']) }}">
                            {{ $link['label'] }}
                        </a>
                    @else
                        <a class="{{ $linkClass($active) }}" href="{{ route($link['route']) }}">
                            {{ $link['label'] }}

                            {{-- subtle active indicator --}}
                            <span
                                class="absolute inset-x-2 -bottom-1 h-0.5 rounded-full transition {{ $active ? 'bg-primary-500' : 'bg-transparent group-hover:bg-slate-300' }}"
                                aria-hidden="true"></span>
                        </a>
                    @endif
                @endforeach
            </div>

            <div class="flex items-center gap-3">
                @if ($isAuth)
                    <livewire:notifications-dropdown />

                    <x-user-avatar :user="Auth::user()" />
                @endif

                <button type="button"
                    class="inline-flex items-center justify-center rounded-md border border-slate-200 bg-white p-1.5 text-slate-700 transition hover:bg-slate-50 hover:text-slate-900 xs:hidden"
                    data-mobile-menu-toggle aria-expanded="false" aria-controls="mobile-nav">
                    <span class="sr-only">Toggle navigation</span>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

    </nav>

    <div id="mobile-nav"
        class="absolute left-0 right-0 top-full hidden border-t border-slate-200/70 bg-white shadow-lg xs:hidden"
        data-mobile-menu>
        <div class="mx-auto w-full max-w-6xl space-y-1 px-6 py-4 text-sm font-medium">
            @foreach (($isAuth ? $authLinks : $guestLinks) as $link)
                    @php $active = request()->routeIs($link['route']); @endphp

                    <a class="flex items-center justify-between rounded-md px-3 py-2 transition-colors {{ $active
                ? 'bg-slate-100 text-slate-900 font-semibold'
                : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900'}} focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                        href="{{ route($link['route']) }}">
                        <span>{{ $link['label'] }}</span>
                        @if ($active)
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-900" aria-hidden="true"></span>
                        @endif
                    </a>
            @endforeach
        </div>
    </div>

</header>

{{-- nav.js --}}