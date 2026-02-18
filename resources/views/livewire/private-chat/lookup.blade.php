<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
	<h2 class="text-base font-semibold text-slate-900">პირადი მიმოწერა</h2>
	<p class="mt-1 text-xs text-slate-500">მხოლოდ ზუსტი ელ.ფოსტის შეყვანით.</p>

	@if (!$isCurrentUserVerified)
		<div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
			პირადი ჩატი ხელმისაწვდომია მხოლოდ სრულად ვერიფიცირებული მომხმარებლისთვის.
		</div>
	@endif

	<form wire:submit.prevent="findRecipient" class="mt-3 space-y-2">
		<label class="block text-sm font-medium text-slate-700" for="private-chat-recipient-email">
			მიმღების ელ.ფოსტა
		</label>
		<input id="private-chat-recipient-email" type="email" wire:model.defer="recipientEmail"
			placeholder="user@example.com"
			class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm"
			@disabled(!$isCurrentUserVerified) />
		@error('recipientEmail')
			<div class="text-xs text-rose-600">{{ $message }}</div>
		@enderror
		<div class="flex items-center justify-end">
			<x-button type="submit" size="sm" variant="secondary" wire:loading.attr="disabled" wire:target="findRecipient"
				:disabled="!$isCurrentUserVerified">
				მოძებნა
			</x-button>
		</div>
	</form>

	@if ($recipientPreview)
		<div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
			<div class="flex items-center gap-2">
				@if ($recipientPreview['avatar'])
					<img src="{{ $recipientPreview['avatar'] }}" alt="{{ $recipientPreview['name'] }}"
						class="h-9 w-9 rounded-full object-cover ring-1 ring-slate-200" />
				@else
					<div
						class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-700">
						{{ mb_strtoupper(mb_substr($recipientPreview['name'], 0, 1)) }}
					</div>
				@endif
				<div class="min-w-0">
					<div class="truncate text-sm font-semibold text-slate-900">{{ $recipientPreview['name'] }}</div>
				</div>
			</div>

			<div class="mt-3">
				<x-button type="button" size="sm" wire:click="startConversation" wire:loading.attr="disabled"
					x-on:click="$dispatch('private-chat-mobile-panels-close')" wire:target="startConversation"
					:disabled="!$isCurrentUserVerified || ($enforceRecipientVerification && !$recipientPreview['is_email_verified'])">
					ჩატის გახსნა
				</x-button>
			</div>
		</div>
	@endif

	@error('chat')
		<div class="mt-2 text-xs text-rose-600">{{ $message }}</div>
	@enderror
</div>