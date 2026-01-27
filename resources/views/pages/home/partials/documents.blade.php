@php
    use Illuminate\Support\Facades\Storage;
@endphp

<section aria-labelledby="public-documents">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @forelse($documents ?? [] as $document)
            @php
                $hasDocument = filled($document->document ?? null);
                $documentPath = $hasDocument ? 'documents/public_documents/' . $document->document : null;
                $documentExists = $documentPath ? Storage::disk('public')->exists($documentPath) : false;
                $isFile = $hasDocument && $documentExists;
                $hasLink = filled($document->link ?? null);

                // Flag items that have neither an existing file nor a link
                $skipDocument = !$isFile && !$hasLink;

                $targetUrl = $isFile
                    ? Storage::disk('public')->url($documentPath)
                    : ($document->link ?? '#');
            @endphp

            @if ($skipDocument)
                @continue
            @endif

            @php
                $baseClasses = 'group flex w-full flex-col justify-center gap-3 rounded-2xl border border-gray-200 bg-blue-100/80 p-3 transition hover:-translate-y-0.5';
            @endphp

            @if ($isFile)
                <button type="button" data-modal-open="document-viewer" data-document-url="{{ $targetUrl }}"
                    data-document-title="{{ $document->name }}" class="{{ $baseClasses }}">
                    <p class="self-center text-base font-semibold text-slate-900 line-clamp-2">
                        {{ $document->name }}
                    </p>

                    <div class="inline-flex h-10 w-10 items-center justify-center self-center rounded-full bg-white">
                        <x-app-icon name="document-text" class="h-5 w-5" aria-hidden="true" />
                    </div>
                </button>
            @else
                <a href="{{ $targetUrl }}" target="_blank" rel="noopener noreferrer" class="{{ $baseClasses }}">
                    <p class="self-center text-base font-semibold text-slate-900 line-clamp-2">
                        {{ $document->name }}
                    </p>

                    <div class="inline-flex h-10 w-10 items-center justify-center self-center rounded-full bg-white">
                        <x-app-icon name="link" class="h-5 w-5" aria-hidden="true" />
                    </div>
                </a>
            @endif
        @empty
            <p class="col-span-full text-center text-sm text-slate-500">
                დოკუმენტები ვერ მოიძებნა
            </p>
        @endforelse
    </div>
</section>