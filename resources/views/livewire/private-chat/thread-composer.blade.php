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

	<x-chat.composer-form placeholder="შეტყობინება..." :disabled="!$isCurrentUserVerified"
		textareaClass="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2 pb-10 pr-10 text-sm text-slate-900 shadow-sm"
		toggleButtonClass="absolute bottom-3 left-2 inline-flex size-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:text-slate-900"
		toggleAriaLabel="დანართების დამატება" contentErrorClass="text-xs text-rose-600"
		:sendDisabled="!$isCurrentUserVerified" uploadOuterClass="max-w-sm"
		:uploadKey="'private-chat-upload-' . $this->getId() . '-' . ($selectedConversationId ?? 'new')"
		uploadHelpText="შეგიძლიათ ატვირთოთ სურათები ან დოკუმენტები." />
</div>
