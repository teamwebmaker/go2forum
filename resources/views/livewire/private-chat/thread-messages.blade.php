<div class="relative bg-slate-50 px-4 py-3">
	<div class="h-[40dvh] space-y-3 overflow-y-auto overscroll-contain pr-1" data-chat-list>
		<div wire:loading.flex wire:target="loadOlder" class="sticky top-0 z-10 -mt-1 mb-2 items-center justify-center">
			<div
				class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-[11px] text-slate-600 ring-1 ring-slate-200">
				<span
					class="inline-block size-3 animate-spin rounded-full border-2 border-slate-300 border-t-slate-600"></span>
				იტვირთება...
			</div>
		</div>

		@forelse ($messages as $message)
			@php
				$isMine = (int) ($message['sender']['id'] ?? 0) === $currentUserId;
				$isDeleted = (bool) ($message['is_deleted'] ?? false);
				$likeCount = (int) ($message['like_count'] ?? 0);
				$likedByMe = (bool) ($message['liked_by_me'] ?? false);
				$createdAt = (string) ($message['created_at_label'] ?? '');
			@endphp
				<div class="w-full">
					<article
						class="min-w-0 w-full max-w-[78%] rounded-2xl border px-3 py-2 shadow-sm {{ $isMine ? 'ml-auto border-primary-200 bg-primary-50/40' : 'mr-auto border-slate-200 bg-white' }}">
					<div class="mb-1 text-[11px] text-slate-500">{{ $createdAt }}</div>
					@if ($isDeleted)
						<div class="break-words text-sm leading-relaxed text-slate-800">
							<span class="italic text-slate-500">
								{{ $isMine ? 'თქვენ წაშალეთ ეს მესიჯი.' : 'ეს მესიჯი წაშლილია.' }}
							</span>
						</div>
					@elseif (filled($message['content'] ?? null))
						<div class="break-words text-sm leading-relaxed text-slate-800">
							{{ $message['content'] }}
						</div>
					@endif

					@if (!$isDeleted && !empty($message['attachments']))
						@php
							$attachments = collect($message['attachments'] ?? []);
							$imageAttachments = $attachments
								->filter(fn($attachment) => ($attachment['type'] ?? '') === 'image' || str_starts_with(($attachment['mime_type'] ?? ''), 'image/'))
								->values()->all();
							$docAttachments = $attachments
								->filter(fn($attachment) => !(($attachment['type'] ?? '') === 'image' || str_starts_with(($attachment['mime_type'] ?? ''), 'image/')))
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
										<div class="relative size-24 overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
											<a href="{{ $attachmentUrl }}" target="_blank" rel="noopener" class="group block h-full w-full">
												<img src="{{ $attachmentUrl }}" alt="{{ $attachment['original_name'] ?? 'image' }}"
													class="h-full w-full object-cover transition group-hover:scale-[1.02]" loading="lazy" />
											</a>
										</div>
									@endforeach
								</div>
						@endif
					@endif

					@if (!$isDeleted)
						<div class="mt-3 flex items-center justify-between gap-2 text-xs text-slate-500">
							<button type="button" wire:click="toggleLike({{ $message['id'] }})" wire:loading.attr="disabled"
								wire:target="toggleLike({{ $message['id'] }})"
								class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 font-semibold ring-1 transition {{ $likedByMe ? 'ring-primary-200 text-primary-600 bg-primary-50/50' : 'ring-slate-200 text-slate-600 hover:ring-slate-300 hover:text-slate-900' }}">
								<x-app-icon name="hand-thumb-up" variant="{{ $likedByMe ? 's' : 'o' }}" class="size-5!" />
								<span class="tabular-nums">{{ $likeCount }}</span>
								<span class="sr-only">Like</span>
							</button>

							@if ($isMine)
								<button type="button" wire:click="deleteMessage({{ $message['id'] }})" wire:loading.attr="disabled"
									wire:target="deleteMessage({{ $message['id'] }})"
									class="rounded-full px-2.5 py-1 text-red-600 ring-1 ring-transparent transition hover:ring-red-200 hover:bg-red-50">
									<x-app-icon name="trash" class="size-4.5!" />
									<span class="sr-only">Delete</span>
								</button>
							@endif
						</div>
					@endif
				</article>
			</div>
		@empty
			<div
				class="rounded-xl border border-dashed border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500">
				მიმოწერა ჯერ ცარიელია.
			</div>
		@endforelse
	</div>

	<div class="pointer-events-none absolute bottom-8 left-1/2 z-10 -translate-x-1/2">
		<x-button type="button" variant="secondary" size="sm" wire:click="loadLatest"
			class="pointer-events-auto rounded-full! border border-slate-200 bg-white/90 text-slate-700 shadow-sm transition duration-200 ease-out hover:bg-white opacity-0 translate-y-2"
			aria-label="Go down" data-chat-go-down>
			<x-slot:icon>
				<x-app-icon name="chevron-down" class="size-4" />
			</x-slot:icon>
		</x-button>
	</div>
</div>
