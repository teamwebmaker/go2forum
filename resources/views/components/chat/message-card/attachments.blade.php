@if (!$isDeleted && !empty($message['attachments']))
    @php
        $attachments = collect($message['attachments'] ?? []);
        $imageAttachments = $attachments
            ->filter(fn($a) => ($a['type'] ?? '') === 'image' || str_starts_with(($a['mime_type'] ?? ''), 'image/'))
            ->values()->all();
        $docAttachments = $attachments
            ->filter(fn($a) => !(($a['type'] ?? '') === 'image' || str_starts_with(($a['mime_type'] ?? ''), 'image/')))
            ->values()->all();
    @endphp

    @if (!empty($docAttachments))
        <ul class="mt-2 space-y-1">
            @foreach ($docAttachments as $attachment)
                @php($attachmentUrl = $attachment['download_url'] ?? $attachment['url'])
                <li class="text-xs text-slate-600">
                    <a class="underline decoration-slate-300 underline-offset-2 hover:text-slate-900"
                        href="{{ $attachmentUrl }}" target="_blank" rel="noopener">
                        {{ $attachment['original_name'] ?? 'attachment' }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    @if (!empty($imageAttachments))
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach ($imageAttachments as $attachment)
                @php($attachmentUrl = $attachment['download_url'] ?? $attachment['url'])

                @if ($isTopic)
                    <div class="relative size-24 sm:size-32">
                        <a href="{{ $attachmentUrl }}" download title="სურათის ჩამოტვირთვა"
                            class="absolute right-1 bottom-1 z-10 rounded-full bg-white/95 px-1.5 py-1 text-[10px] font-semibold text-slate-700 shadow-sm ring-1 ring-black/5 hover:bg-white sm:right-2 sm:bottom-2 sm:px-2">
                            <x-app-icon name="cloud-arrow-down" class="size-3" />
                        </a>

                        <a href="{{ $attachmentUrl }}"
                            class="group block aspect-square overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                            <img src="{{ $attachmentUrl }}" alt="{{ $attachment['original_name'] ?? 'image' }}"
                                class="h-full w-full object-cover transition group-hover:scale-[1.02]" loading="lazy" />
                        </a>
                    </div>
                @else
                    <div class="relative size-20 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 sm:size-24">
                        <a href="{{ $attachmentUrl }}" download title="სურათის ჩამოტვირთვა"
                            class="absolute right-0 bottom-0 z-10 rounded-full bg-white/95 px-1.5 py-1 text-[10px] font-semibold text-slate-700 shadow-sm ring-1 ring-black/5 hover:bg-white">
                            <x-app-icon name="cloud-arrow-down" class="size-3" />
                        </a>

                        <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener" class="group block h-full w-full">
                            <img src="{{ $attachmentUrl }}" alt="{{ $attachment['original_name'] ?? 'image' }}"
                                class="h-full w-full object-cover transition group-hover:scale-[1.02]" loading="lazy" />
                        </a>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
@endif
