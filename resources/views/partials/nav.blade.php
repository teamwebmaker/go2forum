<header class="border-b border-slate-200 bg-white">
    <nav class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4">
        <a class="text-lg font-semibold tracking-tight text-slate-900" href="/">
            {{ config('app.name', default: 'go2forum') }}
        </a>
        <div class="flex items-center gap-6 text-sm font-medium text-slate-600">
            @if (!Auth::user())
                <a class="transition text-gray-700 hover:text-slate-900" href="{{ route('page.home') }}">სახლი</a>
                <a class="transition text-gray-700 hover:text-slate-900" href="{{ route('page.login') }}">შესვლა</a>
                <a class="transition text-gray-700 hover:text-slate-900" href="{{ route('page.register') }}">რეგისტრაცია</a>
            @else
                <form method="GET" action="{{ route('auth.logout') }}">
                    @csrf
                    <x-button type="submit" variant="ghost" class="p-0!">
                        გასვლა
                    </x-button>
                </form>
            @endif
        </div>
    </nav>
</header>