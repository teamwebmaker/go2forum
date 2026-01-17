@extends('layouts.user-profile')

@section('title', 'ვერიფიკაცია')

@section('profile-content')
<div class="mx-auto w-full max-w-xl px-4 py-8">
  <div class="mb-6">
    <h1 class="text-xl font-semibold text-slate-900">ვერიფიკაცია</h1>
    <p class="mt-1 text-sm text-slate-600">
      უსაფრთხოებისთვის დაადასტურეთ ელ.ფოსტა და ტელეფონი.
    </p>
  </div>

  <div class="space-y-4">

    {{-- EMAIL CARD --}}
    @if($is_email_verification_enabled)
      @php
        $emailState = $email_verified ? 'verified' : ($email_pending ? ($email_expired ? 'expired' : 'pending') : 'unverified');

        $emailStatusText = match ($emailState) {
          'verified' => 'თქვენი ელ.ფოსტა ვერიფიკაციებულია.',
          'pending' => 'ვერიფიკაციის წერილი გამოგზავნილია ელ.ფოსტაზე.',
          'expired' => 'ვერიფიკაციის ბმული ვადაგასულია. გთხოვთ ხელახლა გაგზავნა.',
          default => 'ელ.ფოსტა არ არის ვერიფიკაციებული.',
        };

        $emailStatusClass = match ($emailState) {
          'verified' => 'text-emerald-600',
          'pending' => 'text-amber-600',
          'expired' => 'text-red-600',
          default => 'text-red-600',
        };

        // When to show a resend button:
        $showEmailResend = !$email_verified; // simplest UX: always allow resend when not verified
        $emailButtonText = $email_pending ? 'ხელახლა გაგზავნა' : 'ვერიფიკაციის გაგზავნა';
      @endphp

      <section class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm ring-1 ring-black/5">
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0 w-full">
            <div class="flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2">
              <h2 class="text-base font-semibold {{ $emailStatusClass }}" aria-live="polite">
                {{ $emailStatusText }}
              </h2>

              <div class="shrink-0">
                @if($email_verified)
                  <x-app-icon name="check-badge" class="w-6 h-6 text-emerald-600" />
                @elseif($showEmailResend)
                  <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <x-button type="submit" variant="secondary" class="rounded-lg!" textClass="text-xs">
                      {{ $emailButtonText }}
                    </x-button>
                  </form>
                @endif
              </div>
            </div>
          </div>
        </div>
      </section>
    @endif


    {{-- PHONE CARD --}}
    @if($is_phone_verification_enabled)
      @php
        $phoneState = $phone_verified ? 'verified' : ($phone_pending ? 'pending' : 'unverified');

        $phoneStatusText = match ($phoneState) {
          'verified' => 'თქვენი ტელეფონის ნომერი დადასტურებულია.',
          'pending' => 'კოდი გამოგზავნილია ტელეფონზე — შეიყვანეთ 6-ციფრიანი კოდი.',
          default => 'ტელეფონი არ არის დადასტურებული.',
        };

        $phoneStatusClass = match ($phoneState) {
          'verified' => 'text-emerald-600',
          'pending' => 'text-amber-600',
          default => 'text-red-600',
        };

        $phoneSendText = $phone_pending ? 'კოდის ხელახლა გაგზავნა' : 'კოდის გაგზავნა';
      @endphp

      <section class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm ring-1 ring-black/5">
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0 w-full">
            <div class="flex items-center justify-between gap-2">
              <h2 class="text-base font-semibold {{ $phoneStatusClass }}" aria-live="polite">
                {{ $phoneStatusText }}
              </h2>

              @if($phone_verified)
                <x-app-icon name="check-badge" class="w-6 h-6 shrink-0 text-emerald-600" />
              @endif
            </div>

            @if(!$phone_verified && $phone_pending)
              <form class="mt-4 space-y-3" method="POST" action="{{ route('verification.phone.verify') }}">
                @csrf

                <x-form.input
                  name="phone_code"
                  label="ვერიფიკაციის კოდი"
                  minlength="6"
                  maxlength="6"
                  inputmode="numeric"
                  autocomplete="one-time-code"
                  placeholder="XXXXXX"
                />

                <x-button type="submit" class="w-full">
                  კოდის დადასტურება
                </x-button>
              </form>
            @endif

            @if(!$phone_verified)
              <form method="POST" action="{{ route('verification.phone.send') }}" class="mt-3">
                @csrf
                <x-button type="submit" variant="secondary" class="w-full">
                  {{ $phoneSendText }}
                </x-button>
              </form>
            @endif

          </div>
        </div>
      </section>
    @endif

  </div>
</div>
@endsection
