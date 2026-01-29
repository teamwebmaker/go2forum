@extends('master')

@section('title', $category->name . ' - თემები')

@php
    use Illuminate\Support\Facades\Auth;

    $currUser = Auth::user();
    $canOpenTopic = $currUser?->isVerified() ?? false;
@endphp

@section('content')
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 px-4 sm:px-6 lg:px-0">
        @include('topics.partials.category-header', ['category' => $category])

        @include('topics.partials.category-toolbar', [
            'canOpenTopic' => $canOpenTopic,
            'search' => $search,
            'scope' => $scope ?? 'all',
        ])

        @include('topics.partials.topic-list', ['topics' => $topics])

        <div class="flex justify-center">
            {{ $topics->links('components.pagination') }}
        </div>
    </div>
@endsection
