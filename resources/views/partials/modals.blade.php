@php
   $supportEmail = config('services.support.email');
   $supportGmailComposeUrl = config('services.support.gmail_compose_url');
@endphp

{{-- Gmail Redirection modal --}}
<x-ui.modal id="support-contact-modal" title="გარე გადამისამართება" headingIcon="arrow-top-right-on-square" size="lg">
   <div class="space-y-5" data-support-contact-modal>
      <p class="text-sm leading-7 text-slate-600 sm:text-base">
         გადამისამართდებით Gmail-ში, სადაც მიმღები უკვე შევსებული იქნება. თუ არ გსურთ გადასვლა,
         შეგიძლიათ უბრალოდ დააკოპიროთ ელფოსტის მისამართი.
      </p>

      <div
         class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-3 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:p-4">
         <div class="min-w-0 flex-1">
            <p
               class="truncate rounded-xl border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-800 shadow-sm">
               {{ $supportEmail }}
            </p>
         </div>

         <x-button type="button" variant="secondary" size="md" data-support-copy-email
            data-support-email="{{ $supportEmail }}" data-default-label="კოპირება" data-copied-label="დაკოპირდა">
            <x-slot:icon>
               <x-app-icon name="document-duplicate" class="size-4" />
            </x-slot:icon>
            კოპირება
         </x-button>
      </div>
   </div>

   <x-slot:footer>
      <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">
         <x-button type="button" variant="secondary" data-modal-close>
            გაუქმება
         </x-button>

         <x-button type="button" data-support-open-gmail data-support-gmail-url="{{ $supportGmailComposeUrl }}">
            <x-slot:icon>
               <x-app-icon name="envelope" class="size-4" />
            </x-slot:icon>
            Gmail-ში გადასვლა
         </x-button>
      </div>
   </x-slot:footer>
</x-ui.modal>