@extends('layouts.user-profile')

@section('title', 'ბეჯები')

@section('profile-content')

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-black/5 space-y-6">
        <header class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">ბეიჯების მნიშვნელობა</h2>
                <p class="text-sm text-slate-600">იხილე როგორ გამოიყურება და რას აღნიშნავს თითოეული ბეიჯი.</p>
            </div>
        </header>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($data as $record)
                <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4 shadow-inner">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :user="$record['user']" size="md" />
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $record['label'] }}</p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-700 leading-relaxed">{{ $record['desc'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="rounded-xl border border-slate-200 max-w-fit px-4 py-3 flex align-center gap-2 ">
            <p class="text-slate-900">ამჟამინდელი ბეიჯი:</p>
            <x-ui.avatar-badge iconClass="{{ $badgeColor }}" iconSizeClass="size-6!" wrapperClass="h-6!"
                badgeClass="inline-block!" />
        </div>
    </section>
@endsection