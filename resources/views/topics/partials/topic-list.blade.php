@php
    use App\Support\BadgeColors;
@endphp

<div class="space-y-3">
    @forelse ($topics as $topic)
        @php
            $user = $topic->user;
            $isDisabled = $topic->status === 'disabled';

            // User badge
            $showUserBadge = $user && ($user->is_expert || $user->is_top_commentator);
            $badgeColor = $user ? BadgeColors::forUser($user) : null;

            // Render <a> when enabled, <div> when disabled (no href="#" anti-pattern)
            $tag = $isDisabled ? 'div' : 'a';
        @endphp

        <{{ $tag }}
            @unless($isDisabled)
                href="{{ route('topics.show', $topic->slug) }}"
            @endunless
            @if($isDisabled)
                aria-disabled="true"
            @endif
            @class([
                'group relative block rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:shadow-md',
                'cursor-not-allowed opacity-70' => $isDisabled,
            ])
            >
            @if ($topic->pinned)
                {{-- Pinned indicator --}}
                <span class="absolute left-0.5 -top-2.5 -translate-x-1/2 inline-flex -rotate-43 size-6 text-primary-500"
                    title="Pinned">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path fill="currentColor" fill-rule="evenodd"
                            d="M19 12.87c0-.47-.34-.85-.8-.98A3 3 0 0 1 16 9V4h1c.55 0 1-.45 1-1s-.45-1-1-1H7c-.55 0-1 .45-1 1s.45 1 1 1h1v5c0 1.38-.93 2.54-2.2 2.89c-.46.13-.8.51-.8.98V13c0 .55.45 1 1 1h4.98l.02 7c0 .55.45 1 1 1s1-.45 1-1l-.02-7H18c.55 0 1-.45 1-1z" />
                    </svg>
                </span>
            @endif

            <div class="flex items-center justify-between gap-3">
                <div class="flex flex-col items-start gap-2 sm:flex-row sm:items-center">
                    {{-- Topic title --}}
                    <div>
                        <p class="text-sm xs:text-base font-semibold text-slate-900 group-hover:text-primary-600">
                            {{ $topic->title }}
                        </p>

                        <p class="flex items-center gap-1 text-xs text-slate-500">
                            <span class="flex items-center gap-0.5 font-medium text-slate-700">
                                @if ($showUserBadge)
                                    <x-ui.avatar-badge iconClass="{{ $badgeColor }}" iconSizeClass="size-2" />
                                @endif

                                {{ $user?->name }} {{ $user?->surname }}
                            </span>
                        </p>
                    </div>

                    @if ($isDisabled)
                        <span
                            class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500 ring-1 ring-slate-200"
                            title="Closed">
                            <x-app-icon name="lock-closed" class="h-3.5 w-3.5" /> დახურული
                        </span>
                    @endif
                </div>

                {{-- Messages count --}}
                <x-ui.messages-count :count="$topic->messages_count" class="text-primary-600" />
            </div>
        </{{ $tag }}>
    @empty
        <div
            class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-8 text-center text-sm text-slate-500">
            თემები ვერ მოიძებნა.
        </div>
    @endforelse
</div>