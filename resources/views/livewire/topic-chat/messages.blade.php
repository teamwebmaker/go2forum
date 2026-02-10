<div class="relative bg-slate-50 rounded-b-2xl px-4 py-4 flex flex-col h-[50dvh] overflow-hidden">
	<div class=" flex-1 min-h-0 space-y-4 overflow-y-auto pr-2 overscroll-contain [scrollbar-gutter:stable]"
		data-chat-list>
		{{-- Loading older indicator --}}
		<div wire:loading.flex wire:target="loadOlder" class="sticky top-0 z-10 -mt-1 mb-2 items-center justify-center">
			<div
				class="inline-flex items-center gap-2 rounded-full bg-slate-100 py-1 px-3 text-[11px] text-slate-600 ring-1 ring-slate-200">
				<span
					class="inline-block size-3 animate-spin rounded-full border-2 border-slate-300 border-t-slate-600"></span>
				იტვირთება...
			</div>
		</div>

		{{-- Chat messages --}}
		@forelse ($messages as $message)
			@php
				$isMine = (int) ($message['sender']['id'] ?? 0) === $currentUserId;
				$isDeleted = (bool) ($message['is_deleted'] ?? false);
				$likeCount = (int) ($message['like_count'] ?? 0);
				$likedByMe = (bool) ($message['liked_by_me'] ?? false);
				$authorLabel = $message['author_label'] ?? ($isMine ? 'მე' : ($message['sender']['name'] ?? 'User'));
				$createdAt = (string) ($message['created_at_label'] ?? '');
			@endphp

			<div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}" wire:key="message-{{ $message['id'] }}">
				<article
					class="max-w-2xl w-full sm:w-[85%] rounded-2xl border px-4 py-3 shadow-sm {{ $isMine ? 'bg-primary-50/40 border-primary-200' : 'bg-white border-slate-200' }} ">
					<div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500">
						<span class="inline-flex items-center gap-1">
							@if (!empty($message['sender']['badge_color']))
								<x-ui.avatar-badge iconClass="{{ $message['sender']['badge_color'] }}" iconSizeClass="size-4!"
									wrapperClass="inline-flex" badgeClass="inline-flex" />
							@endif
							<span class="font-semibold pt-0.5 text-slate-800">{{ $authorLabel }}</span>
						</span>
						<span class="tabular-nums">{{ $createdAt }}</span>
					</div>

					<div class="mt-2 text-sm leading-relaxed text-slate-800 wrap-break-word">
						@if ($isDeleted)
							<span class="text-slate-500 italic">
								{{ $isMine ? 'თქვენ წაშალაეთ ეს მესიჯი.' : 'ეს მესიჯი წაშლილია ავტორის მიერ.' }}
							</span>
						@else
							{{ $message['content'] ?? '' }}
						@endif
					</div>

					{{-- Attachments --}}
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
									<li class="text-xs text-slate-600">
										<a class="underline decoration-slate-300 underline-offset-2 hover:text-slate-900"
											href="{{ $attachment['url'] }}" target="_blank" rel="noopener">
											{{ $attachment['original_name'] ?? 'attachment' }}
										</a>
									</li>
								@endforeach
							</ul>
						@endif

						@if (!empty($imageAttachments))
							<div class="mt-2 flex flex-wrap gap-2">
								@foreach ($imageAttachments as $attachment)
									<div class="relative size-32">
										<a href="{{ $attachment['url'] }}" download title="სურათის ჩამოტვირთვა"
											class="absolute right-2 bottom-2 z-10 rounded-full bg-white/95 px-2 py-1 text-[10px] font-semibold text-slate-700 shadow-sm ring-1 ring-black/5 hover:bg-white">
											<x-app-icon name="cloud-arrow-down" class="size-3" />
										</a>

										<a href="{{ $attachment['url'] }}"
											class="group block aspect-square overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
											<img src="{{ $attachment['url'] }}" alt="{{ $attachment['original_name'] ?? 'image' }}"
												class="h-full w-full object-cover transition group-hover:scale-[1.02]" loading="lazy" />
										</a>
									</div>
								@endforeach
							</div>
						@endif
					@endif

					@if (!$isDeleted)
						<div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
							{{-- Like --}}
							@if ($currentUserId)
									<button type="button" wire:click="toggleLike({{ $message['id'] }})" wire:loading.attr="disabled"
										wire:target="toggleLike({{ $message['id'] }})" class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-sm font-medium ring-1 transition-colors {{ $likedByMe
								? 'ring-primary-300 bg-primary-50 text-primary-700'
								: 'ring-slate-200 text-slate-700 hover:bg-slate-50 hover:text-slate-700 hover:ring-slate-300'}}">
										<x-app-icon name="hand-thumb-up" variant="{{ $likedByMe ? 's' : 'o' }}" class="size-3 opacity-80" />
										<span class="tabular-nums text-xs">{{ $likeCount }}</span>
										<span class="sr-only">Like</span>
									</button>

							@else
								<span
									class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-slate-500 ring-1 ring-slate-200">
									<x-app-icon name="hand-thumb-up" variant="o" class="size-3" />
									<span class="tabular-nums">{{ $likeCount }}</span>
								</span>
							@endif

							{{-- Delete --}}
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
			<div class="mt-4 text-center text-sm text-slate-500">კომენტარები ჯერ არ არის.</div>
		@endforelse
	</div>

	{{-- Go down --}}
	<div class="pointer-events-none absolute bottom-4 left-1/2 z-10 -translate-x-1/2">
		<x-button type="button" variant="secondary" size="sm" wire:click="loadLatest"
			class="pointer-events-auto rounded-full! border border-slate-200 bg-white/90 text-slate-700 shadow-sm ring-1 ring-black/5 transition duration-200 ease-out hover:bg-white opacity-0 translate-y-2"
			aria-label="Go down" data-chat-go-down>
			<x-slot:icon>
				<x-app-icon name="chevron-down" class="size-4" />
			</x-slot:icon>
		</x-button>
	</div>
</div>