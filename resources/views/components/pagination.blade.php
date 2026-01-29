@if ($paginator->hasPages())
    @php
        /* ---------- Base ---------- */
        $btnBase = implode(' ', [
            'inline-flex',
            'items-center',
            'justify-center',
            'gap-1.5',
            'select-none',
            'h-8',
            'min-w-8',
            'rounded-lg',
            'text-sm',
            'font-semibold',
            'transition',
            'duration-150',
            'ease-out'
        ]);

        /* Sizing */
        $btnNumber = $btnBase . ' px-3';   // page numbers
        $btnArrow = $btnBase . ' px-2';   // arrows lighter

        /* States */
        $link = 'text-slate-600 bg-gray-200 hover:bg-gray-300';
        $disabled = 'text-slate-400 bg-slate-100 cursor-not-allowed';
        $active = 'text-white bg-primary-500';

        /* Combined */
        $numberLink = $btnNumber . ' ' . $link;
        $numberActive = $btnNumber . ' ' . $active;

        $arrowLink = $btnArrow . ' ' . $link;
        $arrowDisabled = $btnArrow . ' ' . $disabled;

        $dots = 'px-2 text-slate-400';
    @endphp

    <nav role="navigation" aria-label="გვერდების ნავიგაცია" class="w-full">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            {{-- Page status --}}
            <p class="text-sm font-medium text-slate-700">
                გვერდი <span class="font-semibold">{{ $paginator->currentPage() }}</span>
                <span class="text-slate-400">/</span>
                <span class="font-semibold">{{ $paginator->lastPage() }}</span>
            </p>

            {{-- ================= MOBILE ================= --}}
            <div class="flex sm:hidden items-center justify-between gap-2">

                @if ($paginator->onFirstPage())
                    <span class="{{ $arrowDisabled }}" aria-disabled="true">
                        <x-app-icon name="chevron-left" class="h-4 w-4" />
                        წინა
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="{{ $arrowLink }}" rel="prev">
                        <x-app-icon name="chevron-left" class="h-4 w-4" />
                        წინა
                    </a>
                @endif

                <span class="text-sm font-semibold text-slate-600 tabular-nums">
                    {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                </span>

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="{{ $arrowLink }}" rel="next">
                        შემდეგი
                        <x-app-icon name="chevron-right" class="h-4 w-4" />
                    </a>
                @else
                    <span class="{{ $arrowDisabled }}" aria-disabled="true">
                        შემდეგი
                        <x-app-icon name="chevron-right" class="h-4 w-4" />
                    </span>
                @endif
            </div>

            {{-- ================= DESKTOP ================= --}}
            <ul class="hidden sm:flex items-center gap-1.5 rounded-xl ring-1 ring-slate-200 bg-white/70 px-2 py-1.5">

                {{-- Previous --}}
                @if ($paginator->onFirstPage())
                    <li>
                        <span class="{{ $arrowDisabled }}" aria-disabled="true">
                            <x-app-icon name="chevron-left" class="h-4 w-4" />
                        </span>
                    </li>
                @else
                    <li>
                        <a href="{{ $paginator->previousPageUrl() }}" class="{{ $arrowLink }}" rel="prev">
                            <x-app-icon name="chevron-left" class="h-4 w-4" />
                        </a>
                    </li>
                @endif

                {{-- Pages --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="{{ $dots }}">…</li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li>
                                    <span class="{{ $numberActive }}" aria-current="page">
                                        {{ $page }}
                                    </span>
                                </li>
                            @else
                                <li>
                                    <a href="{{ $url }}" class="{{ $numberLink }}">
                                        {{ $page }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <li>
                        <a href="{{ $paginator->nextPageUrl() }}" class="{{ $arrowLink }}" rel="next">
                            <x-app-icon name="chevron-right" class="h-4 w-4" />
                        </a>
                    </li>
                @else
                    <li>
                        <span class="{{ $arrowDisabled }}" aria-disabled="true">
                            <x-app-icon name="chevron-right" class="h-4 w-4" />
                        </span>
                    </li>
                @endif
            </ul>

        </div>
    </nav>
@endif