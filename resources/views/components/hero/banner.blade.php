@props([
   'banner' => null,
])
@php
   $title = $banner?->title ?? 'ფორუმი';
   $subtitle = $banner?->subtitle ?? 'აღმოაჩინე კატეგორიები და სასარგებლო საჯარო დოკუმენტები.';
   $image = $banner?->resolved_image_url;
   $position = $banner?->position ?? '50% 40%';
   $overlayClass = $banner?->overlay_class ?? 'bg-cyan-950/70';
   $containerClass = $banner?->container_class ?? 'mb-2';
@endphp

<div {{ $attributes->merge(['class' => trim('relative z-1 flex w-full flex-col gap-2 text-center ' . $containerClass)]) }}>
   @if(filled($image))
      <div aria-hidden="true"
         class="pointer-events-none absolute -top-16 left-1/2 h-40 w-screen -translate-x-1/2 overflow-hidden sm:h-36 sm:-top-14">
         <div class="absolute inset-0 bg-cover bg-center brightness-110"
            style="background-image: url('{{ $image }}'); background-position: {{ $position }};">
         </div>
         <div class="absolute inset-0 {{ $overlayClass }}"></div>
      </div>
   @endif

   <div class="relative z-10 flex w-full flex-col gap-2 text-center">
      <h1 class="text-3xl font-semibold tracking-tight text-white">{{ $title }}</h1>
      @if(filled($subtitle))
         <p class="text-sm text-gray-50">{{ $subtitle }}</p>
      @endif
   </div>
</div>
