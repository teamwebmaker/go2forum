@if ($canPost)
	<div class="rounded-b-2xl border-slate-100 bg-white px-4 py-4 {{ $composerOpen ? '' : 'hidden' }}">
		<x-chat.composer-form placeholder="თქვენი მესიჯი..." formClass="space-y-3" bodyClass="flex flex-col gap-3"
			inputColumnClass="flex-1" uploadColumnClass="w-full max-w-sm space-y-3"
			textareaClass="w-full resize-none rounded-2xl border border-slate-200 bg-white px-3 py-2 pr-10 text-sm text-slate-900 shadow-sm transition pb-10"
			toggleButtonClass="absolute bottom-4 left-2 inline-flex size-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:text-slate-900"
			toggleAriaLabel="Toggle attachments" contentErrorClass="mt-1 text-xs text-rose-600" sendInline
			sendWrapperClass="flex align-self-end justify-end" sendButtonVariant="primary" sendButtonSize="md"
			sendButtonClass="min-w-27.5" uploadOuterClass=""
			:uploadKey="'topic-chat-upload-' . $this->getId() . '-' . $topic->id"
			:replyContext="$replyToContext" cancelReplyAction="cancelReply" />
	</div>
@else
	<div class="border-t border-slate-100 bg-slate-50 px-4 py-4 text-sm text-slate-600">
		ამ თემაზე კომენტარის დამატება შეზღუდულია
	</div>
@endif
