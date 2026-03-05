<div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
	<x-chat.user-identity :name="$activeRecipient['name'] ?? 'პირადი ჩატი'" secondary="პირადი მიმოწერა"
		:avatar="$activeRecipient['avatar'] ?? null" :badgeIcon="$activeRecipient['badge_icon'] ?? null"
		:badgeColor="$activeRecipient['badge_color'] ?? ''" badgePlacement="inline" :showFallbackAvatar="false"
		wrapperClass="flex min-w-0 items-center gap-2" textWrapperClass="min-w-0"
		nameClass="truncate text-sm font-semibold text-slate-900" secondaryClass="text-xs text-slate-500"
		avatarSizeClass="h-9 w-9 text-xs" />
	<x-button type="button" size="sm" variant="secondary" class="lg:hidden" x-on:click="$dispatch('private-chat-mobile-panels-open')">
		ჩატები
	</x-button>
</div>
