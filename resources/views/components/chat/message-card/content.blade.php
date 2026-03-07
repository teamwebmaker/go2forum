<div class="mt-1.5 text-sm leading-relaxed text-slate-800 wrap-break-word sm:mt-2">
    @if (!$isDeleted && $replyTo)
        <div class="mb-2 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2">
            <p class="text-[11px] font-semibold text-slate-700">
                პასუხი: {{ $replyAuthorLabel }}
            </p>
            <p class="text-xs text-slate-600">
                {{ $replyToDeleted ? 'ეს მესიჯი წაშლილია.' : $replyPreviewText }}
            </p>
        </div>
    @endif

    @if ($isDeleted)
        <span class="text-slate-500 italic">
            {{ $isTopic ? ($isMine ? 'თქვენ წაშალაეთ ეს მესიჯი.' : 'ეს მესიჯი წაშლილია ავტორის მიერ.') : ($isMine ? 'თქვენ წაშალეთ ეს მესიჯი.' : 'ეს მესიჯი წაშლილია.') }}
        </span>
    @elseif ($isEditing)
        <div class="space-y-2">
            <textarea wire:model.defer="editContent" rows="3"
                class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm transition"></textarea>

            @if ((int) $editingMessageId === (int) ($message['id'] ?? 0))
                @php
                    $editError = $errors->first('editContent');
                @endphp
                @if ($editError)
                    <p class="text-xs text-rose-600">{{ $editError }}</p>
                @endif
            @endif

            <div class="flex justify-end gap-2">
                <button type="button" wire:click="cancelEditMessage"
                    class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
                    გაუქმება
                </button>
                <button type="button" wire:click="saveEditedMessage" wire:loading.attr="disabled"
                    wire:target="saveEditedMessage"
                    class="inline-flex items-center rounded-md border border-primary-200 bg-primary-50 px-3 py-1.5 text-xs font-semibold text-primary-700 transition hover:border-primary-300 hover:bg-primary-100">
                    შენახვა
                </button>
            </div>
        </div>
    @else
        {{ $message['content'] ?? '' }}
    @endif
</div>
