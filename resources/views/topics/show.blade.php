@extends('master')
@section('title', $topic->title)
@section('content')
    @php
        $backUrl = $topic->category
            ? route('categories.topics', $topic->category)
            : route('page.home');
    @endphp
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 px-4 sm:px-6 lg:px-0">
        <a href="{{ $backUrl }}"
            class="inline-flex items-center gap-1 text-sm font-medium text-slate-600 transition hover:text-slate-900">
            <x-app-icon name="chevron-left" class="h-4 w-4" />
            უკან დაბრუნება
        </a>

        <livewire:topic-chat :topic="$topic" :can-post="$canPost" />
    </div>
@endsection
