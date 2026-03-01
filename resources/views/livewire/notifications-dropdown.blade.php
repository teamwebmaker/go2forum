@php
    $panelId = 'notifications-panel-' . $this->getId();
    $headingId = 'notifications-heading-' . $this->getId();
@endphp

<div class="relative z-50" x-data="{ panelOpen: $wire.entangle('open').live, wasOpen: false }" x-effect="
        if (panelOpen && !wasOpen) { $nextTick(() => $refs.panel?.focus()) }
        if (!panelOpen && wasOpen) { $nextTick(() => $refs.trigger?.focus()) }
        wasOpen = panelOpen
    " @keydown.escape.window="if (panelOpen) { $wire.closePanel() }">
    <x-button type="button" variant="secondary" size="sm" wire:click="togglePanel"
        class="min-h-5 min-w-5 rounded-full! border-none! p-1.5! text-slate-600 ring-1 ring-offset-2 ring-slate-200 hover:text-slate-900"
        x-ref="trigger" aria-label="Notifications" aria-haspopup="dialog" aria-controls="{{ $panelId }}"
        x-bind:aria-expanded="panelOpen ? 'true' : 'false'">
        <x-slot:icon>
            <x-app-icon name="bell" class="size-5" />
        </x-slot:icon>
    </x-button>

    @if ($unreadCount > 0)
        <span
            class="absolute -right-1.5 -top-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white">
            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
        </span>
    @endif

    <div x-cloak x-show="panelOpen" x-transition.opacity @click="$wire.closePanel()"
        class="fixed inset-0 z-40 bg-slate-900/20 sm:hidden"></div>

    <div id="{{ $panelId }}" role="dialog" aria-modal="false" aria-labelledby="{{ $headingId }}" tabindex="-1"
        x-ref="panel" x-cloak x-show="panelOpen" x-transition.origin.top.right @click.outside="$wire.closePanel()"
        class="fixed inset-x-2 top-16 z-50 mx-auto w-auto max-w-md rounded-xl border border-slate-200 bg-white shadow-lg sm:absolute sm:inset-x-auto sm:right-0 sm:top-auto sm:mx-0 sm:mt-2 sm:w-80">
        <div class="flex items-center justify-between border-b border-slate-100 px-3 py-2">
            <span id="{{ $headingId }}" class="text-sm font-semibold text-slate-700">შეტყობინებები</span>
            @if ($totalCount > $pruneThreshold)
                <x-ui.tooltip position="bottom"
                    text="შეტყობინებები ივსება! გთხოვთ გაწმინდოთ იგი. დაიმახსოვრეთ ისტორიის გასუფთავება მხოლოდ ბოლო {{ $pruneKeep }} შეტყობინებას შეინარჩუნებს.">
                    <x-button type="button" variant="ghost" size="sm" wire:click="clearHistory" wire:loading.attr="disabled"
                        wire:loading.class="opacity-60" wire:target="clearHistory"
                        class="pr-0 text-rose-600 hover:text-rose-700" textClass="text-xs font-semibold">
                        გაწმენდა
                    </x-button>
                </x-ui.tooltip>
            @endif
        </div>

        <div class="max-h-[55dvh] overflow-y-auto sm:max-h-80" role="list">
            @forelse ($notificationItems as $notification)
                @php
                    $notificationId = (string) ($notification['id'] ?? '');
                    $data = $notification['data'] ?? [];
                    $isUnread = (bool) ($notification['is_unread'] ?? false);
                    $isTopicNotification = !empty($data['topic_id']);
                    $isPrivateNotification = !empty($data['conversation_id']);
                @endphp
                <div class="relative border-b border-slate-100 px-3.5 py-3.5 text-sm transition hover:bg-slate-50 {{ $isUnread ? 'bg-slate-100' : '' }}"
                    wire:key="notification-{{ $notificationId }}" role="listitem">
                    <a href="{{ route('notifications.visit', $notificationId) }}" class="block pr-11">
                        @if ($isTopicNotification)
                            <div class="text-xs font-semibold text-slate-700">
                                {{ $data['sender_name'] ?? 'Someone' }} დააკომენტარა
                            </div>
                            <div class="mt-0.5 text-xs text-slate-600">
                                {{ $data['topic_title'] ?? 'Topic' }}
                            </div>
                        @elseif ($isPrivateNotification)
                            <div class="text-xs font-semibold text-slate-700">
                                {{ $data['sender_name'] ?? 'Someone' }} მოგწერათ პირადში
                            </div>
                            <div class="mt-0.5 text-xs text-slate-600">
                                პირადი მიმოწერა
                            </div>
                        @else
                            <div class="text-xs font-semibold text-slate-700">
                                {{ $data['sender_name'] ?? 'Someone' }} ახალი შეტყობინება
                            </div>
                        @endif
                        @if (!empty($data['preview']))
                            <div class="mt-1 text-[11px] text-slate-500">{{ $data['preview'] }}</div>
                        @endif
                        <div class="mt-1 text-[11px] text-slate-500/90">
                            {{ $notification['created_at_human'] ?? '' }}
                        </div>
                    </a>

                    <x-button type="button" variant="ghost" size="sm"
                        wire:click.stop="deleteNotification('{{ $notificationId }}')" wire:loading.attr="disabled"
                        wire:loading.class="opacity-60" wire:target="deleteNotification('{{ $notificationId }}')"
                        aria-label="Delete notification"
                        class="absolute right-1.5 top-1.5 rounded-full p-2 text-slate-400 hover:text-rose-600">
                        <x-slot:icon>
                            <x-app-icon name="x-mark" class="h-4 w-4" />
                        </x-slot:icon>
                    </x-button>
                </div>
            @empty
                <div class="px-3 py-6 text-center text-xs text-slate-500">
                    შეტყობინებები არ არის.
                </div>
            @endforelse
        </div>

        <div
            class="flex items-center rounded-b-xl border-t border-slate-200 bg-white text-sm font-medium text-slate-700">
            <x-button type="button" variant="ghost" size="md" wire:click="markAllRead" wire:loading.attr="disabled"
                wire:loading.class="opacity-60" wire:target="markAllRead" :disabled="$unreadCount <= 0"
                class="flex-1 rounded-none border-x border-slate-200 py-3.5 text-slate-600 hover:bg-slate-100 hover:text-slate-700"
                textClass="text-xs font-semibold">
                ყველას წაკითხულად მონიშვნა
            </x-button>
        </div>
    </div>
</div>