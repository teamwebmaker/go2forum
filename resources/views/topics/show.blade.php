@extends('master')
@section('title', $topic->title)
@section('content')
@php
    $backUrl = $topic->category
        ? route('categories.topics', $topic->category)
        : route('page.home');
@endphp
<div class="mx-auto max-w-4xl px-4 py-8">
    <a href="{{ $backUrl }}"
        class="inline-flex items-center gap-1 text-sm font-medium text-slate-600 transition hover:text-slate-900">
        <x-app-icon name="chevron-left" class="h-4 w-4" />
        უკან დაბრუნება
    </a>
    <h1 class="mt-4 text-2xl font-semibold text-slate-900">{{ $topic->title }}</h1>
    @if ($topic->category)
        <a href="{{ route('categories.topics', $topic->category) }}"
            class="mt-2 inline-flex max-w-max items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
            {{ $topic->category->name }}
        </a>
    @endif
    <p class="mt-2 text-sm text-slate-500">Topic page placeholder.</p>
</div>
@endsection
