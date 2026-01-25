@extends('master')

@section('title', 'ფორუმი')

@section('content')
   <div class="relative flex w-full flex-col gap-8 overflow-visible">
      <x-hero.banner title="ფორუმი" subtitle="აღმოაჩინე კატეგორიები და სასარგებლო საჯარო დოკუმენტები."
         :image="asset('images/hero-banner.jpg')" position="50% 30%" overlay="bg-cyan-950/70" class="mb-2" />

      <div class="flex w-full flex-col gap-8 pt-4">
         <div class="grid grid-cols-1 gap-3 lg:grid-cols-[1.1fr_auto_1fr] lg:items-start">
            @if(!empty($categories))
               @include('pages.home.partials.categories', ['categories' => $categories])
            @endif
            <div aria-hidden="true"
               class="my-2 w-full border-t border-slate-200 lg:my-0 lg:h-full lg:w-px lg:border-l lg:border-t-0 lg:mx-4">
            </div>
            @if (!empty($documents))
               @include('pages.home.partials.documents', ['documents' => $documents])
            @endif

         </div>
      </div>

      {{-- Document preview modal (reuses global modal component) --}}
      <x-ui.modal id="document-viewer" title="დოკუმენტი:" size="6xl">
         <div class="space-y-3">

            <div class="min-w-0">
               <p class="text-md font-semibold text-slate-900" data-modal-heading>---</p>
               <p class="text-xs text-slate-500">ფაილი იხსნება ქვემოთ მოცემულ ფანჯარაში.</p>
            </div>


            <div class="overflow-hidden rounded-lg ring-1 ring-slate-200">
               <iframe src="" title="Document preview" data-document-frame allow="fullscreen"
                  class="h-[70vh] w-full bg-slate-50" loading="lazy"></iframe>
            </div>
         </div>
      </x-ui.modal>
   </div>
@endsection

@push('scripts')
   @vite(['resources/js/home/documentPreviewModal.js'])
@endpush