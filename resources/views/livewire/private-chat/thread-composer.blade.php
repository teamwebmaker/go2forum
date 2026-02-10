<div class="border-t border-slate-100 bg-white px-4 py-3">
	@if (!$isCurrentUserVerified)
		<div class="mb-2 text-xs text-amber-700">
			შეტყობინების გაგზავნა შესაძლებელია მხოლოდ ვერიფიცირებული მომხმარებლისთვის.
		</div>
	@endif
	@error('chat')
		<div class="mb-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
			{{ $message }}
		</div>
	@enderror
	<form wire:submit.prevent="sendMessage" class="space-y-2" x-data="{ showUploads: $wire.entangle('showUploads') }">
		<div class="relative">
			<textarea wire:model.defer="content" rows="3" placeholder="შეტყობინება..."
				class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2 pb-10 pr-10 text-sm text-slate-900 shadow-sm"
				@disabled(!$isCurrentUserVerified)></textarea>
			<button type="button"
				class="absolute bottom-3 left-2 inline-flex size-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:text-slate-900"
				aria-label="დანართების დამატება" @click="showUploads = !showUploads" @disabled(!$isCurrentUserVerified)>
				<span x-show="!showUploads">
					<x-app-icon name="plus" class="size-4" />
				</span>
				<span x-show="showUploads">
					<x-app-icon name="plus" class="size-4 rotate-45" />
				</span>
			</button>
		</div>

		@error('content')
			<div class="text-xs text-rose-600">{{ $message }}</div>
		@enderror

			<div x-show="showUploads" x-collapse class="max-w-sm">
				<livewire:upload-field wire:model="attachments" label="" :multiple="true"
					:key="'private-chat-upload-' . $this->getId() . '-' . ($selectedConversationId ?? 'new')"
					:max-size="(int) config('chat.attachments_max_kb', 20480)"
					help-text="შეგიძლიათ ატვირთოთ სურათები ან დოკუმენტები." />
			</div>

		<div class="flex justify-end">
			<x-button type="submit" wire:loading.attr="disabled" wire:target="sendMessage, attachments"
				:disabled="!$isCurrentUserVerified">
				<span wire:loading.remove wire:target="sendMessage, attachments">გაგზავნა</span>
				<span wire:loading wire:target="sendMessage, attachments">იგზავნება...</span>
			</x-button>
		</div>
	</form>
</div>
