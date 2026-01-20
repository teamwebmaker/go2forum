@extends('layouts.auth')

@section('title', 'პაროლის აღდგენა')

@section('auth-content')
    <header class="space-y-2 text-center">
        <h1 class="text-2xl font-semibold">პაროლის აღდგენა</h1>
        <p class="text-sm text-slate-600">შეიყვანეთ ელ.ფოსტა და მიიღეთ აღდგენის ბმული.</p>
    </header>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <x-form.input name="email" type="email" label="ელ.ფოსტა" placeholder="jane@example.com" required />

        <x-button type="submit" class="w-full">
            ბმულის გაგზავნა
        </x-button>

        <p class="text-end text-sm text-slate-600">
            გაიხსენე პაროლი?
            <a class="font-medium text-blue-600 underline" href="{{ route('login') }}">შესვლა.</a>
        </p>
    </form>
@endsection