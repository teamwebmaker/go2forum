@props([
    'name',
    'variant' => 'o', // o|s|m|c
])

@php
    $component = "heroicon-{$variant}-{$name}";
@endphp

<x-dynamic-component :component="$component" {{ $attributes->class('w-5 h-5') }} />
