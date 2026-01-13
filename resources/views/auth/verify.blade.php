@extends('layouts.auth')

@section('title', 'Verify')

@section('content')
    <header class="space-y-2">
        <h1 class="text-2xl font-semibold">Verify your email</h1>
        <p class="text-sm text-slate-600">Enter the 6-digit code sent to your inbox.</p>
    </header>

    <form class="space-y-4">
        <label class="block text-sm font-medium text-slate-700">Verification code</label>
        <div class="grid grid-cols-6 gap-2">
            @for ($i = 1; $i <= 6; $i++)
                <x-form.input
                    name="code_{{ $i }}"
                    type="text"
                    minlength="1"
                    maxlength="1"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    inputClass="text-center text-lg font-semibold h-12"
                />
            @endfor
        </div>

        <x-button type="submit" class="w-full">
            Verify
        </x-button>

        <p class="text-center text-sm text-slate-600">
            Didn’t get a code?
            <button type="button" class="font-medium text-slate-900 hover:underline">Resend</button>
        </p>
    </form>
@endsection
