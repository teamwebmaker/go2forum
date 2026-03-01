@extends('layouts.user-profile')

@section('title', 'ბეჯები')

@section('profile-content')
    @php
        $currentBadgeIcon = \App\Support\BadgeColors::iconForUser($user);
        $currentBadgeColor = \App\Support\BadgeColors::forUser($user);
        $currentBadgeLabel = $user->is_expert
            ? 'ექსპერტი'
            : ($user->is_top_commentator ? 'ტოპ კომენტატორი' : null);
    @endphp

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-black/5">
        <header class="border-b border-slate-200 bg-slate-50/60 px-6 py-5">
            <div class="flex flex-col gap-4">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold text-slate-900">ბეჯები და სტატუსები</h2>
                </div>
            </div>
        </header>

        <div class="space-y-6 p-6">
            <article class="rounded-xl border border-slate-200 bg-slate-50/40 px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :user="$user" size="lg" :showBadges="false" />

                        @if ($currentBadgeIcon)
                            <x-ui.avatar-badge iconName="{{ $currentBadgeIcon }}" iconClass="{{ $currentBadgeColor }}"
                                iconSizeClass="size-7!" wrapperClass="inline-flex" badgeClass="inline-flex" />
                        @endif
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-base font-semibold text-slate-900">ამჟამინდელი სტატუსი</h3>

                        @if ($currentBadgeLabel)
                            <p class="text-sm font-medium text-slate-800">{{ $currentBadgeLabel }}</p>
                            <p class="text-sm text-slate-600">შენი ბეჯი ჩანს ავატართან თემებში, ჩატებში და პროფილში.</p>
                        @else
                            <p class="text-sm text-slate-600">ამჟამად ბეჯი არ გაქვს. როცა სტატუსს მიიღებ, აქ გამოჩნდება.</p>
                        @endif
                    </div>
                </div>
            </article>

            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($data as $record)
                    @php
                        $badgeExampleUser = $record['user'] ?? null;
                        $recordBadgeIcon = \App\Support\BadgeColors::iconForUser($badgeExampleUser);
                        $recordBadgeColor = \App\Support\BadgeColors::forUser($badgeExampleUser);
                        $isCurrentBadge = $currentBadgeIcon && $recordBadgeIcon === $currentBadgeIcon;
                    @endphp

                    <article
                        class="rounded-xl border p-4 transition sm:p-5 {{ $isCurrentBadge ? 'border-slate-300 bg-slate-50/80 ring-1 ring-slate-200' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                        <div class="flex items-start gap-3">
                            <div class="relative shrink-0">
                                <x-ui.avatar :user="$user" size="md" :showBadges="false" />
                                @if ($recordBadgeIcon)
                                    <x-ui.avatar-badge iconName="{{ $recordBadgeIcon }}" iconClass="{{ $recordBadgeColor }}"
                                        iconSizeClass="size-5!" wrapperClass="absolute -right-0.5 -bottom-0" />
                                @endif
                            </div>

                            <div class="min-w-0 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-semibold text-slate-900">{{ $record['label'] }}</h3>
                                    @if ($isCurrentBadge)
                                        <span
                                            class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                                            აქტიური
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm leading-relaxed text-slate-700">{{ $record['desc'] }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection