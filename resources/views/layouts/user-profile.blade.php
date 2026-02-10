@extends('master')

@section('title', 'პროფილი')

@section('content')
   <div class="w-full space-y-6">
      <header>
         <h1 class="text-2xl font-semibold">ჩემი პროფილი</h1>
      </header>

      <nav class="space-y-2 text-sm font-medium sm:flex sm:flex-wrap sm:items-center sm:justify-between sm:gap-2 sm:space-y-0">
         <div class="flex flex-wrap gap-2">
            <a href="{{ route('profile.user-info') }}"
               class="inline-flex min-h-10 items-center justify-center rounded-full border px-3 py-2 transition sm:min-h-0 sm:py-1.5 {{ request()->routeIs('profile.user-info') ? ' border-gray-300 bg-gray-200 text-slate-900' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300' }}">
               ინფორმაცია
            </a>

            @if (Auth::user()->isVerified())
               <a href="{{ route('profile.messages') }}"
                  class="inline-flex min-h-10 items-center justify-center rounded-full border px-3 py-2 transition sm:min-h-0 sm:py-1.5 {{ request()->routeIs('profile.messages') ? ' border-gray-300 bg-gray-200 text-slate-900' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300' }}">
                  პირადი ჩატი
               </a>
            @endif

            @if (Auth::user()->shouldVerify())
               <a href="{{ route('profile.verification') }}"
                  class="relative inline-flex min-h-10 items-center justify-center rounded-full border px-3 py-2 transition sm:min-h-0 sm:py-1.5 {{ request()->routeIs('profile.verification') ? ' border-gray-300 bg-gray-200 text-slate-900' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300' }}">
                  ვერიფიკაცია
                  @if (!Auth::user()->isVerified())
                     <span class="absolute -right-0.5 top-0.5 h-2 w-2 rounded-full animate-ping bg-rose-500"></span>
                     <span class="absolute -right-0.5 top-0.5 h-2 w-2 rounded-full  bg-rose-500"></span>
                  @endif
               </a>
            @endif
         </div>

         <a href="{{ route('profile.badges') }}"
            class="inline-flex min-h-10 w-full items-center justify-center rounded-full border px-3 py-2 transition sm:min-h-0 sm:w-auto sm:py-1.5 {{ request()->routeIs('profile.badges') ? ' border-gray-300 bg-gray-200 text-slate-900' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300' }}">
            მიღწევები
         </a>

      </nav>

      @yield('profile-content')
   </div>
@endsection
