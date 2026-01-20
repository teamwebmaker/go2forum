@extends('master')

@section('title', '419')

@section('content')
    <div class="mx-auto flex w-full max-w-2xl flex-col items-center justify-center text-center">
        <div class="relative mb-8 flex h-24 w-24 items-center justify-center">
            <span class="absolute inset-0 rounded-full bg-primary-100"></span>
            <span class="absolute inset-2 rounded-full bg-primary-200"></span>
            <span class="relative text-2xl font-semibold text-primary-700">419</span>
        </div>

        <h1 class="text-3xl font-semibold text-slate-900">სესიის ვადა ამოიწურა</h1>
        <p class="mt-3 text-base text-slate-600">
            {{ $message ?? 'განაახლეთ გვერდი და სცადეთ თავიდან.' }}
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <button type="button"
                onclick="window.location.reload()"
                class="inline-flex items-center rounded-md bg-primary-500 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-primary-600/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900/20">
                გვერდის განახლება
            </button>
            <a href="{{ route('page.home') }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400/20">
                მთავარ გვერდზე
            </a>
        </div>
    </div>
@endsection
