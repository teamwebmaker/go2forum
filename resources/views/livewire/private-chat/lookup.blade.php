<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
	<h2 class="text-base font-semibold text-slate-900">პირადი მიმოწერა</h2>
	<p class="mt-1 text-xs text-slate-500">მხოლოდ ზედმეტსახელით მოძებნა.</p>

	@if (!$isCurrentUserVerified)
		<div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
			პირადი ჩატი ხელმისაწვდომია მხოლოდ სრულად ვერიფიცირებული მომხმარებლისთვის.
		</div>
	@endif

	<form wire:submit.prevent="findRecipient" class="mt-3 space-y-2">
		<label class="block text-sm font-medium text-slate-700" for="private-chat-recipient-nickname">
			მიმღების ზედმეტსახელი
		</label>
		<input id="private-chat-recipient-nickname" type="text" wire:model.defer="recipientNickname"
			placeholder="nickname"
			class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm"
			@disabled(!$isCurrentUserVerified) />
		@error('recipientNickname')
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
			<x-chat.user-identity :name="$recipientPreview['name']" :avatar="$recipientPreview['avatar'] ?? null"
				:showBadge="false" wrapperClass="flex items-center gap-2" textWrapperClass="min-w-0"
				nameClass="truncate text-sm font-semibold text-slate-900" avatarSizeClass="h-9 w-9 text-xs"
				avatarFallbackClass="rounded-full bg-slate-200 font-semibold text-slate-700" />

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
