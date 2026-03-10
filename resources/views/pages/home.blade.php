@extends('master')

@section('title', 'ფორუმი')

@section('content')
   <div class="relative flex w-full flex-col gap-8 overflow-visible">
      <x-hero.banner :banner="$banner" />

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
