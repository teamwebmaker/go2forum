<section class="grid gap-4 lg:grid-cols-3" data-private-chat-livewire data-component-id="{{ $this->getId() }}"
	data-has-more="{{ $hasMore ? '1' : '0' }}" x-data="{ mobilePanelsOpen: {{ $chatOpen ? 'false' : 'true' }} }"
	@private-chat-mobile-panels-close.window="mobilePanelsOpen = false"
	@private-chat-mobile-panels-open.window="mobilePanelsOpen = true">
	<div class="order-1 lg:hidden">
		<x-button type="button" size="sm" variant="secondary"
			@click="
				const next = !mobilePanelsOpen;
				mobilePanelsOpen = next;
				if (next) {
					$nextTick(() => $refs.mobilePanels?.scrollIntoView({
						behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
						block: 'start'
					}));
				}
			"
			x-bind:aria-expanded="mobilePanelsOpen ? 'true' : 'false'" class="w-full justify-center">
			<span x-text="mobilePanelsOpen ? 'პანელის დამალვა' : 'ჩატები და მიმღების ძიება'"></span>
		</x-button>
	</div>

	<div class="order-3 lg:order-1 lg:col-span-1">
		<div x-ref="mobilePanels" class="space-y-4 hidden lg:block" x-cloak
			x-bind:class="mobilePanelsOpen ? '!block' : 'hidden'">
			@include('livewire.private-chat.lookup')
			@include('livewire.private-chat.conversations')
		</div>
	</div>

	<div
		class="order-2 flex min-h-104 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:order-2 lg:col-span-2">
		@if ($chatOpen)
			@include('livewire.private-chat.thread-header')
			@include('livewire.private-chat.thread-messages')
			@include('livewire.private-chat.thread-composer')
		@else
			@include('livewire.private-chat.thread-empty')
		@endif
	</div>
</section>

@push('scripts')
	@vite(['resources/js/pages/privateChat.js'])
@endpush
