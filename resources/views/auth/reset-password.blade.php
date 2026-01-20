@extends('layouts.auth')

@section('title', 'პაროლის განახლება')

@section('auth-content')
    <header class="space-y-2 text-center">
        <h1 class="text-2xl font-semibold">პაროლის განახლება</h1>
        <p class="text-sm text-slate-600">შეიყვანეთ ახალი პაროლი.</p>
    </header>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-form.input name="email" type="email" label="ელ.ფოსტა" placeholder="jane@example.com" :value="$email" required />

        <x-form.input name="password" type="password" label="ახალი პაროლი" placeholder="••••••••" required />

        <x-form.input name="password_confirmation" type="password" label="გაიმეორეთ პაროლი" placeholder="••••••••"
            required />

        <x-button type="submit" class="w-full">
            პაროლის განახლება
        </x-button>

        <p class="text-end text-sm text-slate-600">
            გაიხსენე პაროლი?
            <a class="font-medium text-blue-600 underline" href="{{ route('login') }}">შესვლა.</a>
        </p>
    </form>
@endsection