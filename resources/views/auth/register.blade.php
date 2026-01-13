@extends('layouts.auth')

@section('title', 'რეგისტრაცია')

@section('auth-content')
    <header class="space-y-2">
        <h1 class="text-2xl font-semibold text-center">ანგარიშის შექმნა</h1>
    </header>

    <form method="POST" action="{{ route('auth.register') }}" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-form.input name="name" label="სახელი" placeholder="Jane" required />
            <x-form.input name="surname" label="გვარი" placeholder="Doe" required />
        </div>

        <x-form.input name="email" type="email" label="ელ.ფოსტა" placeholder="jane@example.com" required />

        <x-form.input name="phone" type="tel" label="ნომერი" placeholder="000 00 00 00" infoMessage="სავალდებულო არაა" />

        <x-form.input name="password" type="password" label="პაროლი" placeholder="••••••••" required />

        <x-form.input name="password_confirmation" type="password" label="პაროლის დადასტურება" placeholder="••••••••"
            required />

        <x-button type="submit" class="w-full">
            ანგარიშის შექმნა
        </x-button>

        <p class="text-end text-sm text-slate-600">
            უკვე გაქვთ ანგარიში?
            <a class="font-medium text-blue-600 hover:underline" href="{{ route('page.login') }}">შესვლა</a>
        </p>
    </form>
@endsection