@props([
   'banner' => null,
])
@php
   $title = $banner?->title ?? 'ფორუმი';
   $subtitle = $banner?->subtitle ?? 'აღმოაჩინე კატეგორიები და სასარგებლო საჯარო დოკუმენტები.';
   $image = $banner?->resolved_image_url;
   $position = $banner?->position ?? '50% 40%';
   $overlayClass = $banner?->overlay_class ?? 'bg-cyan-950/70';
   $containerClass = $banner?->container_class ?? '';
@endphp
<div class="relative w-full overflow-hidden rounded-2xl {{ $containerClass }}">
    @if (filled($image))
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-cover brightness-110"
                 style="background-image:url('{{ $image }}'); background-position: {{ $position }};"></div>
            <div class="absolute inset-0 {{ $overlayClass }}"></div>
        </div>
    @endif

    <div class="relative z-10 mx-auto flex min-h-35 w-full max-w-4xl flex-col justify-center px-4 py-5 text-center sm:min-h-42.5 sm:px-6 sm:py-8">
        <h1 class="text-2xl font-semibold leading-tight wrap-break-words text-white sm:text-3xl">{{ $title }}</h1>
        @if (filled($subtitle))
            <p class="mt-2 text-sm leading-relaxed wrap-break-words text-gray-50">{{ $subtitle }}</p>
        @endif
    </div>
</div>