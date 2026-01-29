@extends('master')
@section('title', $topic->title)
@section('content')
<div class="mx-auto max-w-4xl px-4 py-8">
    <h1 class="text-2xl font-semibold text-slate-900">{{ $topic->title }}</h1>
    <p class="text-sm text-slate-500 mt-2">Topic page placeholder.</p>
</div>
@endsection
