@if ($canPost)
	<div class="rounded-b-2xl border-slate-100 bg-white px-4 py-4 {{ $composerOpen ? '' : 'hidden' }}">
		<form wire:submit.prevent="sendMessage" class="space-y-3" x-data="{ showUploads: $wire.entangle('showUploads') }">

			<div class="flex flex-col gap-3">
				{{-- Message input --}}
				<div class="flex-1">
					<div class="relative">
						<textarea wire:model.defer="content" rows="3" placeholder="თქვენი მესიჯი..."
							class="w-full resize-none rounded-2xl border border-slate-200 bg-white px-3 py-2 pr-10 text-sm text-slate-900 shadow-sm transition pb-10"></textarea>
						<button type="button"
							class="absolute bottom-4 left-2 inline-flex size-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:text-slate-900"
							aria-label="Toggle attachments" @click="showUploads = !showUploads">
							<span x-show="!showUploads">
								<x-app-icon name="plus" class="size-4 " />
							</span>
							<span x-show="showUploads">
								<x-app-icon name="plus" class="size-4 rotate-45" />
							</span>
						</button>
					</div>

					@error('content')
						<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
					@enderror
					<div class="flex align-self-end justify-end">
						<x-button type="submit" variant="primary" size="md" wire:loading.attr="disabled"
							wire:target="sendMessage, attachments" class="min-w-27.5">
							<span wire:loading.remove wire:target="sendMessage, attachments">
								გაგზავნა
							</span>
							<span wire:loading wire:target="sendMessage, attachments">
								იგზავნება...
							</span>
						</x-button>
					</div>
				</div>

				{{-- Upload + send --}}
					<div class="w-full max-w-sm space-y-3">
						<div x-show="showUploads" x-collapse>
							<livewire:upload-field wire:model="attachments" label="" :multiple="true"
								:key="'topic-chat-upload-' . $this->getId() . '-' . $topic->id"
								:max-size="(int) config('chat.attachments_max_kb', 20480)" />
						</div>
					</div>
			</div>

		</form>
	</div>
@else
	<div class="border-t border-slate-100 bg-slate-50 px-4 py-4 text-sm text-slate-600">
		ამ თემაზე კომენტარის დამატება შეზღუდულია
	</div>
@endif
