@php
    use App\Models\PublicDocument;

    $viewer = auth()->user();
@endphp

<section aria-labelledby="public-documents">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @forelse($documents ?? [] as $document)
            @php
                $hasDocument = filled($document->document ?? null);
                $fileLocation = $hasDocument ? $document->resolveStorageLocation() : null;
                $isFile = $hasDocument && $fileLocation !== null;
                $hasLink = filled($document->link ?? null);
                $externalLink = $hasLink && filter_var((string) $document->link, FILTER_VALIDATE_URL)
                    ? (string) $document->link
                    : null;
                $canView = $document->canBeViewedBy($viewer);
                $canDownload = $document->canBeDownloadedBy($viewer);

                // Flag items that have neither an existing file nor a link
                $skipDocument = !$isFile && !$hasLink;

                $openUrl = route('public-documents.open', ['publicDocument' => $document]);
                $downloadUrl = $isFile && $canDownload
                    ? route('public-documents.download', ['publicDocument' => $document])
                    : null;
                $trackViewUrl = $document->requires_auth_to_view
                    ? route('public-documents.track-view', ['publicDocument' => $document])
                    : null;
            @endphp

            @if ($skipDocument)
                @continue
            @endif

            @php
                $baseClasses = 'group flex w-full flex-col justify-center gap-3 rounded-2xl border border-gray-200 bg-blue-100/80 p-3 transition hover:-translate-y-0.5';
            @endphp

            @if ($isFile)
                @if ($canView)
                    <button type="button" data-modal-open="document-viewer" data-document-url="{{ $openUrl }}"
                        data-document-title="{{ $document->name }}"
                        @if ($downloadUrl) data-document-download-url="{{ $downloadUrl }}" @endif
                        @if ($externalLink) data-document-link-url="{{ $externalLink }}" @endif
                        @if (!$canDownload) data-document-hide-native-download="1" @endif
                        class="{{ $baseClasses }}">
                        <p class="self-center text-base font-semibold text-slate-900 line-clamp-2">
                            {{ $document->name }}
                        </p>

                        <div class="inline-flex h-10 w-10 items-center justify-center self-center rounded-full bg-white">
                            <x-app-icon name="document-text" class="h-5 w-5" aria-hidden="true" />
                        </div>
                    </button>
                @else
                    <button type="button" data-modal-open="document-auth-required"
                        @if ($trackViewUrl) data-document-track-url="{{ $trackViewUrl }}" @endif
                        class="{{ $baseClasses }}">
                        <p class="self-center text-base font-semibold text-slate-900 line-clamp-2">
                            {{ $document->name }}
                        </p>

                        <div class="inline-flex h-10 w-10 items-center justify-center self-center rounded-full bg-white">
                            <x-app-icon name="document-text" class="h-5 w-5" aria-hidden="true" />
                        </div>
                    </button>
                @endif
            @else
                @if ($canView)
                    <a href="{{ $openUrl }}" target="_blank" rel="noopener noreferrer" class="{{ $baseClasses }}">
                        <p class="self-center text-base font-semibold text-slate-900 line-clamp-2">
                            {{ $document->name }}
                        </p>

                        <div class="inline-flex h-10 w-10 items-center justify-center self-center rounded-full bg-white">
                            <x-app-icon name="link" class="h-5 w-5" aria-hidden="true" />
                        </div>
                    </a>
                @else
                    <button type="button" data-modal-open="document-auth-required"
                        @if ($trackViewUrl) data-document-track-url="{{ $trackViewUrl }}" @endif
                        class="{{ $baseClasses }}">
                        <p class="self-center text-base font-semibold text-slate-900 line-clamp-2">
                            {{ $document->name }}
                        </p>

                        <div class="inline-flex h-10 w-10 items-center justify-center self-center rounded-full bg-white">
                            <x-app-icon name="link" class="h-5 w-5" aria-hidden="true" />
                        </div>
                    </button>
                @endif
            @endif
        @empty
            <p class="col-span-full text-center text-sm text-slate-500">
                დოკუმენტები ვერ მოიძებნა
            </p>
        @endforelse
    </div>
</section>
