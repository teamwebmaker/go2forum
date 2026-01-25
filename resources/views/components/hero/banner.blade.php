@props([
    'title',
    'subtitle' => null,
    'image' => null,
    'position' => '50% 30%',
    'overlay' => 'bg-cyan-950/60',
    'class' => '',
])

<div {{ $attributes->merge(['class' => trim('relative flex w-full flex-col gap-2 text-center '.$class)]) }}>
   <div aria-hidden="true"
      class="pointer-events-none absolute -top-16 left-1/2 h-40 w-screen -translate-x-1/2 overflow-hidden sm:h-36 sm:-top-14">
      <div class="absolute inset-0 bg-cover bg-center brightness-110"
         style="background-image: url('{{ $image ?? asset('images/banner-1.jpg') }}'); background-position: {{ $position }};">
      </div>
      <div class="absolute inset-0 {{ $overlay }}"></div>
   </div>

   <div class="relative z-10 flex w-full flex-col gap-2 text-center">
      <h1 class="text-3xl font-semibold tracking-tight text-white">{{ $title }}</h1>
      @isset($subtitle)
         <p class="text-sm text-gray-50">{{ $subtitle }}</p>
      @endisset
   </div>
</div>
