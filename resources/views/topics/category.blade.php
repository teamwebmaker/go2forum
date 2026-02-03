@extends('master')

@section('title', $category->name . ' - თემები')

@section('content')
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 px-4 sm:px-6 lg:px-0">
        <a href="{{ route('page.home') }}"
            class="inline-flex items-center gap-1 text-sm font-medium text-slate-600 transition hover:text-slate-900">
            <x-app-icon name="chevron-left" class="h-4 w-4" />
            უკან დაბრუნება
        </a>
        @include('topics.partials.category-header', ['category' => $category])

        @include('topics.partials.category-toolbar', [
            'search' => $search,
            'scope' => $scope ?? 'all',
        ])
           @include('topics.partials.topic-list', ['topics' => $topics])
        <div class="flex justify-center">
                {{ $topics->links('components.pagination') }}
            </div>
        </div>
@endsection
