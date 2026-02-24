@props([
    'text',
    'position' => 'top',
    'size' => 'auto', // auto|xs|sm|md
    'triggerClass' => '',
    'titleClasses' => '',
])
@php
    $sizeClasses = match ($size) {
        'xs' => 'w-40',
        'sm' => 'w-64',
        'md' => 'w-80',
        default => 'w-auto max-w-[calc(100vw-1rem)] sm:max-w-xs',
    };

       $positionClasses = match ($position) {
        'bottom' => 'top-full mt-2 left-1/2 -translate-x-1/2',
        'left'   => 'bottom-full mb-2 left-1/2 -translate-x-1/2 sm:right-full sm:mr-2 sm:top-1/2 sm:-translate-y-1/2 sm:left-auto sm:translate-x-0',
        'right'  => 'bottom-full mb-2 left-1/2 -translate-x-1/2 sm:left-full sm:ml-2 sm:top-1/2 sm:-translate-y-1/2 sm:translate-x-0',
        default  => 'bottom-full mb-2 left-1/2 -translate-x-1/2',
    };

@endphp

    <span class="relative inline-flex group">
        <span class="{{ $triggerClass }}">
            {{ $slot }}
        </span>
    <span
  class="invisible absolute z-10 {{ $sizeClasses }} {{ $positionClasses }} {{ $titleClasses }}
    rounded-md border border-slate-200 bg-white px-2 py-1 text-xs text-slate-700 shadow-md
    whitespace-normal break-words leading-snug
    opacity-0 transition group-hover:visible group-hover:opacity-100">
  {{ $text }}
</span>
    </span>
