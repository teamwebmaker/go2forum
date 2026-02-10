<section class="w-full" data-topic-chat-livewire data-component-id="{{ $this->getId() }}"
	data-has-more="{{ $hasMore ? '1' : '0' }}">
	<div class="flex min-h-96 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
		@include('livewire.topic-chat.header')
		@include('livewire.topic-chat.messages')
		@include('livewire.topic-chat.composer')
	</div>
</section>

@push('scripts')
	@vite(['resources/js/pages/topicChat.js'])
@endpush