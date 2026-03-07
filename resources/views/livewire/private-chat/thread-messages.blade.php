<x-chat.thread-feed containerClass="relative bg-slate-50 px-3 py-3 sm:px-4"
	listClass="h-[52dvh] sm:h-[40dvh] space-y-3 overflow-y-auto overscroll-contain pr-1" goDownWrapperClass="bottom-6 sm:bottom-8">
	@forelse ($messages as $message)
		<x-chat.message-card :message="$message" :currentUserId="$currentUserId" variant="private"
			:canReply="$isCurrentUserVerified && (bool) $currentUserId" wire:key="message-{{ $message['id'] }}" />
	@empty
		<div class="rounded-xl border border-dashed border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500">
			მიმოწერა ჯერ ცარიელია.
		</div>
	@endforelse
</x-chat.thread-feed>
