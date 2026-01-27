@extends('layouts.auth')

@section('title', 'რეგისტრაცია')

@section('auth-content')
    <header class="space-y-2">
        <h1 class="text-2xl font-semibold text-center">ანგარიშის შექმნა</h1>
    </header>

    <form method="POST" action="{{ route('auth.register') }}" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-form.input name="name" label="სახელი" placeholder="Jane" minlength="2" required />
            <x-form.input name="surname" label="გვარი" placeholder="Doe" minlength="2" required />
        </div>

        <x-form.input name=" email" type="email" label="ელ.ფოსტა" placeholder="jane@example.com" :displayError="false"
            required />


        <x-form.input name="phone" type="tel" label="ნომერი" placeholder="000 00 00 00" iconPosition="left"
            iconPadding="pl-12" inputmode="numeric" :displayError="false"
            pattern="^(\\+995\\s?)?(\\d{3}\\s?\\d{3}\\s?\\d{3}|\\d{3}\\s?\\d{2}\\s?\\d{2}\\s?\\d{2})$"
            :required="$is_phone_verification_enabled" :infoMessage="$is_phone_verification_enabled ? '' : 'სავალდებულო არაა'">
            <x-slot name="icon">
                <span class="text-sm">+995</span>
            </x-slot>
        </x-form.input>


        <x-form.input name="password" type="password" label="პაროლი" placeholder="••••••••" required />

        <x-form.input name="password_confirmation" type="password" label="პაროლის დადასტურება" placeholder="••••••••"
            required />

        <x-button type="submit" class="w-full">
            ანგარიშის შექმნა
        </x-button>

        <p class="text-end text-sm text-slate-600">
            უკვე გაქვთ ანგარიში?
            <a class="font-medium text-blue-600 underline" href="{{ route('login') }}">შესვლა.</a>
        </p>
    </form>
@endsection