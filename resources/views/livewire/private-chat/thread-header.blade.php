<div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
	<div class="flex min-w-0 items-center gap-2">
		@if (!empty($activeRecipient['avatar']))
			<img src="{{ $activeRecipient['avatar'] }}" alt="{{ $activeRecipient['name'] }}"
				class="h-9 w-9 rounded-full object-cover ring-1 ring-slate-200" />
		@endif
			<div class="min-w-0">
				<div class="flex min-w-0 items-center gap-1 text-sm font-semibold text-slate-900">
					@if (!empty($activeRecipient['badge_color']))
						<x-ui.avatar-badge iconClass="{{ $activeRecipient['badge_color'] }}" iconSizeClass="size-4!"
							wrapperClass="inline-flex shrink-0" badgeClass="inline-flex" />
					@endif
					<span class="truncate">{{ $activeRecipient['name'] ?? 'პირადი ჩატი' }}</span>
				</div>
				<div class="text-xs text-slate-500">პირადი მიმოწერა</div>
			</div>
		</div>
	<x-button type="button" size="sm" variant="secondary" class="lg:hidden" x-on:click="$dispatch('private-chat-mobile-panels-open')">
		ჩატები
	</x-button>
</div>
