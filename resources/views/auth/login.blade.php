@extends('layouts.auth')

@section('title', 'ანგარიშში შესვლა')

@section('auth-content')
    <header class="space-y-2">
        <h1 class="text-2xl font-semibold text-center">შესვლა</h1>
        <p class="text-center text-sm text-slate-600">
            არ გაქვთ ანგარიში?
            <a class="font-medium text-blue-600 underline" href="{{ route('register') }}">ანგარიშის შექმნა.</a>
        </p>
    </header>

    <form method="POST" action="{{ route('auth.login') }}">
        @csrf

        <div class="space-y-4">
            <x-form.input name="email" type="email" label="ელ.ფოსტა" placeholder="jane@example.com" required />

            <x-form.input name="password" type="password" label="პაროლი" minlength="6" placeholder="••••••••" required />
        </div>

        <p class="text-end text-sm text-slate-600 mt-1.5">
            <a class="font-medium text-blue-600 underline" href="{{ route('password.request') }}">
                დაგავიწყდა პაროლი?
            </a>
        </p>
        <x-button type="submit" class="w-full mt-4">
            შესვლა
        </x-button>



    </form>
@endsection