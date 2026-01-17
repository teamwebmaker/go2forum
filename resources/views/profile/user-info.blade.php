@extends('layouts.user-profile')

@section('title', 'პროფილი')

@section('profile-content')
    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <h2 class="text-sm font-semibold text-slate-900">ძირითადი ინფორმაცია</h2>
        <div class="mt-3 space-y-2 text-sm text-slate-700">
            <p><span class="font-medium">სახელი:</span> {{ Auth::user()->name }}</p>
            <p><span class="font-medium">გვარი:</span> {{ Auth::user()->surname }}</p>
            <p><span class="font-medium">ელფოსტა:</span> {{ Auth::user()->email }}</p>
            @if (Auth::user()->phone)
                <p><span class="font-medium">ტელეფონი:</span> {{ Auth::user()->phone }}</p>
            @endif
        </div>
    </section>
@endsection
