@extends('master')

@section('title', 'ფორუმი')

@section('content')
   <div class="relative flex w-full flex-col gap-4 sm:gap-8 overflow-visible">
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
         <div class="space-y-4" data-document-viewer>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
               <div class="min-w-0">
                  <p class="text-md font-semibold text-slate-900" data-modal-heading>---</p>
               </div>
               <div class="flex shrink-0 flex-wrap items-center gap-2">
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

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-inner">
               <div class="relative">
                  <div class="m-4 hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
                     data-document-error>
                     დოკუმენტის ჩატვირთვა ვერ მოხერხდა. სცადეთ ხელახლა ან გახსენით ბმულით.
                  </div>

                  <div class="max-h-[70vh] overflow-y-auto overscroll-contain p-1 sm:p-4" data-document-scroll-region>
                     <div class="flex min-h-72 items-center justify-center rounded-2xl bg-white/70 p-4"
                        data-document-loading>
                        <div class="h-10 w-10 animate-spin rounded-full border-4 border-slate-200 border-t-slate-600">
                        </div>
                     </div>

                     <div class="hidden space-y-4" data-document-pages aria-live="polite"></div>
                  </div>
               </div>
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