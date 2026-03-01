<div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
	<div class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">ჩატები</div>
	<div class="space-y-1.5">
		@forelse ($conversations as $conversation)
			@php
				$isActive = (int) $selectedConversationId === (int) ($conversation['id'] ?? 0);
				$other = $conversation['other_user'] ?? null;
				$lastMessageAt = !empty($conversation['last_message_at'])
					? \Carbon\Carbon::parse($conversation['last_message_at'])->diffForHumans()
					: 'ჯერ ცარიელია';
			@endphp
			<button type="button" wire:click="openConversation({{ (int) $conversation['id'] }})"
				x-on:click="$dispatch('private-chat-mobile-panels-close')"
				class="w-full rounded-xl border px-3 py-2 text-left transition {{ $isActive ? 'border-primary-300 bg-primary-50' : 'border-slate-200 bg-white hover:bg-slate-50' }}">
				<div class="flex items-center gap-2">
					@if (!empty($other['avatar']))
						<img src="{{ $other['avatar'] }}" alt="{{ $other['name'] }}"
							class="h-8 w-8 rounded-full object-cover ring-1 ring-slate-200" />
					@else
						<div
							class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-[11px] font-semibold text-slate-700">
							{{ mb_strtoupper(mb_substr($other['name'] ?? 'U', 0, 1)) }}
						</div>
					@endif
						<div class="min-w-0 flex-1">
							<div class="flex min-w-0 items-center gap-1 text-sm font-medium text-slate-800">
								@if (!empty($other['badge_icon']))
									<x-ui.avatar-badge iconName="{{ $other['badge_icon'] }}"
										iconClass="{{ $other['badge_color'] }}" iconSizeClass="size-4!"
										wrapperClass="inline-flex shrink-0" badgeClass="inline-flex" />
								@endif
								<span class="truncate">{{ $other['name'] ?? 'Unknown user' }}</span>
							</div>
							<div class="text-xs text-slate-500">{{ $lastMessageAt }}</div>
						</div>
					</div>
			</button>
		@empty
			<div class="rounded-xl border border-dashed border-slate-200 px-3 py-5 text-center text-xs text-slate-500">
				პირადი ჩატები ჯერ არ გაქვთ.
			</div>
		@endforelse
	</div>
	@if ($hasMoreConversations)
		<div class="mt-3">
			<x-button
				type="button"
				size="sm"
				variant="secondary"
				wire:click="loadMoreConversations"
				wire:loading.attr="disabled"
				wire:target="loadMoreConversations"
				class="w-full justify-center"
			>
				მეტის ჩატვირთვა
			</x-button>
		</div>
	@endif
</div>
