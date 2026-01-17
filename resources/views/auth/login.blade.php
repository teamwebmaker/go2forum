@extends('layouts.auth')

@section('title', 'ანგარიშში შესვლა')

@section('auth-content')
    <header class="space-y-2">
        <h1 class="text-2xl font-semibold text-center">შესვლა</h1>
    </header>

    <form method="POST" action="{{ route('auth.login') }}" class="space-y-4">
        @csrf
        <x-form.input name="email" type="email" label="ელ.ფოსტა" placeholder="jane@example.com" required />

        <x-form.input name="password" type="password" label="პაროლი" minlength="6" placeholder="••••••••" required />

        <x-button type="submit" class="w-full">
            შესვლა
        </x-button>

        <p class="text-end text-sm text-slate-600">
            ახალი ხარ?
            <a class="font-medium text-blue-600 hover:underline" href="{{ route('register') }}">ანგარიშის შექმნა</a>
        </p>
    </form>
@endsection