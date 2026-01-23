@extends('layouts.user-profile')

@section('title', 'პროფილი')

@section('profile-content')
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-black/5">
        <header class="flex items-start flex-col sm:flex-row justify-between gap-4">
            <div class="space-y-1">
                <h2 class="text-base font-semibold text-slate-900">ძირითადი ინფორმაცია</h2>
                <p class="text-sm text-slate-600">ნახე და განაახლე პირადი დეტალები.</p>
                <div class="text-sm border-t border-slate-200 text-gray-500">
                    ბოლოს განახლდა:  {{ $user->updated_at->locale('ka')->translatedFormat('d M Y') }}
                </div>
            </div>

            @unless ($isEditing)
                <a href="{{ route('profile.user-info', ['edit' => 1]) }}"
                    class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400/20">
                    რედაქტირება
                </a>
            @endunless
        </header>

        @if (!$isEditing)
            <div class="mt-6">
                @include('profile._avatar-section', [
                    'user' => $user,
                    'avatarUrl' => $avatarUrl,
                    'avatarInitial' => $avatarInitial,
                    'isEditing' => false,
                    'isVerified' => $isVerified,
                ])
            </div>
        @endif

        @if (!$isEditing)
            <dl class="mt-6 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                <div class="space-y-1 rounded-lg border border-slate-200/70 bg-slate-50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">სახელი</dt>
                    <dd class="font-medium text-slate-900">{{ $user->name }}</dd>
                </div>

                <div class="space-y-1 rounded-lg border border-slate-200/70 bg-slate-50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">გვარი</dt>
                    <dd class="font-medium text-slate-900">{{ $user->surname }}</dd>
                </div>

                <div class="space-y-1 rounded-lg border border-slate-200/70 bg-slate-50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">ელ.ფოსტა</dt>
                    <dd class="font-medium text-slate-900">{{ $user->email }}</dd>
                </div>

                <div class="space-y-1 rounded-lg border border-slate-200/70 bg-slate-50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">ტელეფონი</dt>
                    <dd class="font-medium text-slate-900">{{ $user->phone ?: '—' }}</dd>
                </div>
            </dl>
        @else
            <form data-dirty-guard method="POST" action="{{ route('profile.user-info.update') }}" enctype="multipart/form-data"
                class="mt-6 space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_editing" value="1">

                @include('profile._avatar-section', [
                    'user' => $user,
                    'avatarUrl' => $avatarUrl,
                    'avatarInitial' => $avatarInitial,
                    'isEditing' => true,
                    'isVerified' => $isVerified,
                ])

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-form.input name="name" label="სახელი" required value="{{ $user->name }}" />
                    <x-form.input name="surname" label="გვარი" required value="{{ $user->surname }}" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-form.input name="email" type="email" label="ელ.ფოსტა" required value="{{ $user->email }}"
                        :infoMessage="$requireEmailVerification
                    ? 'ცვლილების შემდეგ საჭირო გახდება ხელახლა ვერიფიკაცია.'
                    : ''" />

                    <x-form.input name="phone" type="tel" label="ტელეფონი" placeholder="000 00 00 00" 
                       pattern="^(\\+995\\s?)?(\\d{3}\\s?\\d{3}\\s?\\d{3}|\\d{3}\\s?\\d{2}\\s?\\d{2}\\s?\\d{2})$" :required="$requirePhoneVerification"
                        value="{{ $user->phone }}" :infoMessage="$requirePhoneVerification
                    ? 'ცვლილების შემდეგ საჭირო გახდება ხელახლა ვერიფიკაცია.'
                    : ''" />
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <x-button type="submit" data-dirty-submit>ცვლილებების შენახვა</x-button>

                    <a href="{{ route('profile.user-info') }}"
                        class="inline-flex items-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400/20">
                        გაუქმება
                    </a>
                </div>
            </form>
        @endif
    </section>
@endsection

{{-- /profile/avatarPreview.js --}}
