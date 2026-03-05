<x-chat.thread-feed containerClass="relative bg-slate-50 rounded-b-2xl px-3 py-3 sm:px-4 sm:py-4 flex flex-col h-[58dvh] sm:h-[50dvh] overflow-hidden"
	listClass="flex-1 min-h-0 space-y-4 overflow-y-auto pr-1 sm:pr-2 overscroll-contain [scrollbar-gutter:stable]"
	goDownWrapperClass="bottom-4"
	goDownButtonClass="pointer-events-auto rounded-full! border border-slate-200 bg-white/90 text-slate-700 shadow-sm ring-1 ring-black/5 transition duration-200 ease-out hover:bg-white opacity-0 translate-y-2">
	{{-- Chat messages --}}
	@forelse ($messages as $message)
		<x-chat.message-card :message="$message" :currentUserId="$currentUserId" variant="topic"
			:editingMessageId="$editingMessageId" wire:key="message-{{ $message['id'] }}" />
	@empty
		<div class="mt-4 text-center text-sm text-slate-500">კომენტარები ჯერ არ არის.</div>
	@endforelse
</x-chat.thread-feed>
