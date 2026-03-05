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
					<x-chat.user-identity :name="$other['name'] ?? 'Unknown user'" :secondary="$lastMessageAt"
						:avatar="$other['avatar'] ?? null" :badgeIcon="$other['badge_icon'] ?? null"
						:badgeColor="$other['badge_color'] ?? ''" badgePlacement="inline"
						wrapperClass="flex min-w-0 flex-1 items-center gap-2"
						textWrapperClass="min-w-0 flex-1" nameClass="truncate text-sm font-medium text-slate-800"
						secondaryClass="text-xs text-slate-500" avatarSizeClass="h-8 w-8 text-[11px]"
						avatarFallbackClass="rounded-full bg-slate-200 font-semibold text-slate-700" />
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
