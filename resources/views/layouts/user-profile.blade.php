@extends('master')

@section('title', 'პროფილი')

@section('content')
   <div class="w-full space-y-6">
      <header>
         <h1 class="text-2xl font-semibold">ჩემი პროფილი</h1>
      </header>

      <nav class="flex flex-wrap justify-between gap-2 text-sm font-medium">
         <div class="space-x-2">
            <a href="{{ route('profile.user-info') }}"
               class="rounded-full border px-3 py-1.5 transition {{ request()->routeIs('profile.user-info') ? ' border-gray-300 bg-gray-200 text-slate-900' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300' }}">
               ინფორმაცია
            </a>

            @if (Auth::user()->shouldVerify())
               <a href="{{ route('profile.verification') }}"
                  class="relative rounded-full border px-3 py-1.5 transition {{ request()->routeIs('profile.verification') ? ' border-gray-300 bg-gray-200 text-slate-900' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300' }}">
                  ვერიფიკაცია
                  @if (!Auth::user()->isVerified())
                     <span class="absolute -right-0.5 top-0.5 h-2 w-2 rounded-full animate-ping bg-rose-500"></span>
                     <span class="absolute -right-0.5 top-0.5 h-2 w-2 rounded-full  bg-rose-500"></span>
                  @endif
               </a>
            @endif
         </div>

         <a href="{{ route('profile.badges') }}"
            class="rounded-full border px-3 py-1.5 transition {{ request()->routeIs('profile.badges') ? ' border-gray-300 bg-gray-200 text-slate-900' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300' }}">
            მიღწევები
         </a>

      </nav>

      @yield('profile-content')
   </div>
@endsection