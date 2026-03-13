@extends('master')

@section('title', 'ფორუმი')

@section('content')
   <div class="relative flex w-full flex-col gap-8 overflow-visible">
      <x-hero.banner :banner="$banner" />

      <div class="-mx-1 flex w-full flex-col gap-8 pt-4 sm:mx-0">
         @php
            $hasCategories = isset($categories) && is_countable($categories) && count($categories) > 0;
            $hasDocuments = isset($documents) && is_countable($documents) && count($documents) > 0;
         @endphp

         @if ($hasCategories || $hasDocuments)
            <div @class([
               'grid grid-cols-1 gap-4 xl:gap-5',
               'lg:grid-cols-[3fr_1fr] lg:items-start' => $hasCategories && $hasDocuments,
            ])>
               @if ($hasCategories)
                  @include('pages.home.partials.categories', ['categories' => $categories])
               @endif

               @if ($hasDocuments)
                  @include('pages.home.partials.documents', ['documents' => $documents])
               @endif
            </div>
         @endif
      </div>

      {{-- Document preview modal (reuses global modal component) --}}
      <x-ui.modal id="document-viewer" title="დოკუმენტი:" size="6xl">
         <div class="space-y-3">
            <div class="flex items-start justify-between gap-3">
               <div class="min-w-0">
                  <p class="text-md font-semibold text-slate-900" data-modal-heading>---</p>
                  <p class="text-xs text-slate-500">ფაილი იხსნება ქვემოთ მოცემულ ფანჯარაში.</p>
               </div>
               <div class="flex shrink-0 items-center gap-2">
                  <a href="#" target="_blank" rel="noopener noreferrer" data-document-link
                     class="hidden rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                     ბმულის გახსნა
                  </a>
                  <a href="#" data-document-download
                     class="hidden rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                     ჩამოტვირთვა
                  </a>
               </div>
            </div>


            <div class="overflow-hidden rounded-lg ring-1 ring-slate-200">
               <iframe src="" title="Document preview" data-document-frame allow="fullscreen"
                  class="h-[70vh] w-full bg-slate-50" loading="lazy"></iframe>
            </div>
         </div>
      </x-ui.modal>

      <x-ui.modal id="document-auth-required" title="დოკუმენტი:" size="md">
         <p class="text-sm font-medium text-slate-800">
            ამ დოკუმენტის სანახავად ავტორიზაციაა საჭირო
         </p>
      </x-ui.modal>
   </div>
@endsection

@push('scripts')
   @vite(['resources/js/home/documentPreviewModal.js'])
@endpush