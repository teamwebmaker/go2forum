<div class="flex flex-wrap items-center gap-2">
    <span class="text-sm text-gray-600 dark:text-gray-300">
        {{ count($selectedTopics) }} არჩეული
    </span>
    <div class="relative" x-data="{
            open: false,
            advanced: false,
            x: 8,
            y: 8,
            triggerEl: null,
            close() { this.open = false; this.advanced = false; },
            toggle(e) {
                this.triggerEl = e.currentTarget;
                this.advanced = false;
                this.open = !this.open;
                if (!this.open) return;
                this.$nextTick(() => this.positionMenu());
            },
            positionMenu() {
                const trigger = this.triggerEl;
                const menu = this.$refs.menu;
                if (!trigger || !menu) return;

                const margin = 8;
                const r = trigger.getBoundingClientRect();
                const menuW = menu.offsetWidth || 352;
                const menuH = menu.offsetHeight || 260;

                this.x = Math.max(margin, Math.min(window.innerWidth - menuW - margin, r.left));

                const preferredY = r.bottom + 8;
                this.y = Math.max(margin, Math.min(window.innerHeight - menuH - margin, preferredY));
            },
            toggleAdvanced() {
                this.advanced = !this.advanced;
                this.$nextTick(() => this.positionMenu());
            },
        }" x-on:keydown.escape.window="close()" x-on:scroll.window="close()"
        x-on:resize.window="open && positionMenu()">
        <x-filament::button size="xs" color="gray" x-on:click="toggle($event)">
            {{ __('models.trash.actions.bulk_actions') }}
        </x-filament::button>

        <template x-teleport="body">
            <div x-show="open" x-cloak class="fixed inset-0 z-40" x-on:click="close()">
                <div x-ref="menu"
                    class="fixed z-50 w-[min(22rem,calc(100vw-1rem))] max-h-[calc(100vh-1rem)] overflow-y-auto overflow-x-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl dark:border-white/10 dark:bg-gray-900"
                    :style="`left:${x}px;top:${y}px;`" x-on:click.stop x-transition.origin.top.left>
                    <button type="button"
                        class="flex w-full items-center px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:text-gray-200 dark:hover:bg-white/5"
                        wire:click="restoreSelectedTopics" x-on:click="close()" @disabled(count($selectedTopics) === 0)>
                        {{ __('models.trash.actions.restore_selected') }}
                    </button>

                    <button type="button" x-show="!advanced"
                        class="flex w-full items-center px-3 py-2 text-left text-sm text-danger-600 hover:bg-danger-50 disabled:cursor-not-allowed disabled:opacity-50 dark:text-danger-400 dark:hover:bg-danger-500/10"
                        wire:click="forceDeleteSelectedTopicsOnly" x-on:click="close()"
                        wire:confirm="{{ __('models.trash.confirmations.force_delete') }}"
                        @disabled(count($selectedTopics) === 0)>
                        {{ __('models.trash.actions.force_delete_selected_topics_only') }}
                    </button>

                    <button type="button" x-show="advanced"
                        class="flex w-full items-center px-3 py-2 text-left text-sm text-danger-600 hover:bg-danger-50 disabled:cursor-not-allowed disabled:opacity-50 dark:text-danger-400 dark:hover:bg-danger-500/10"
                        wire:click="forceDeleteSelectedTopicsWithThread" x-on:click="close()"
                        wire:confirm="{{ __('models.trash.confirmations.force_delete') }}"
                        @disabled(count($selectedTopics) === 0)>
                        {{ __('models.trash.actions.force_delete_selected_topics_with_thread') }}
                    </button>

                    <div class="my-1 border-t border-gray-200 dark:border-white/10"></div>
                    <button type="button"
                        class="flex w-full items-center px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5"
                        x-on:click="toggleAdvanced()">
                        <span x-show="!advanced">{{ __('models.trash.actions.advanced') }}</span>
                        <span x-show="advanced">{{ __('models.trash.actions.basic') }}</span>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>
<div class="overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50 dark:bg-white/5">
            <tr class="text-left">
                <th class="px-4 py-3">
                    <input type="checkbox" wire:model.live="selectAllTopics"
                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                </th>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">{{ __('models.topics.fields.title') }}</th>
                <th class="px-4 py-3">{{ __('models.topics.fields.category_id') }}</th>
                <th class="px-4 py-3">{{ __('models.topics.fields.deleted_at') }}</th>
                <th class="px-4 py-3">{{ __('models.trash.actions.label') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->deletedTopics as $topic)
                <tr class="border-t border-gray-100 dark:border-white/10">
                    <td class="px-4 py-3">
                        <input type="checkbox" wire:model.live="selectedTopics" value="{{ $topic->id }}"
                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    </td>
                    <td class="px-4 py-3">{{ $topic->id }}</td>
                    <td class="px-4 py-3">{{ $topic->title }}</td>
                    <td class="px-4 py-3">{{ $topic->category?->name ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $topic->deleted_at?->format('Y-m-d H:i') ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <div class="relative inline-block text-left" x-data="{
                                        open: false,
                                        advanced: false,
                                        x: 8,
                                        y: 8,
                                        triggerEl: null,
                                        close() { this.open = false; this.advanced = false; },
                                        toggle(e) {
                                            this.triggerEl = e.currentTarget;
                                            this.advanced = false;
                                            this.open = !this.open;
                                            if (!this.open) return;
                                            this.$nextTick(() => this.positionMenu());
                                        },
                                        positionMenu() {
                                            const trigger = this.triggerEl;
                                            const menu = this.$refs.menu;
                                            if (!trigger || !menu) return;

                                            const margin = 8;
                                            const r = trigger.getBoundingClientRect();
                                            const menuW = menu.offsetWidth || 256;
                                            const menuH = menu.offsetHeight || 280;

                                            this.x = Math.max(margin, Math.min(window.innerWidth - menuW - margin, r.right - menuW));

                                            const preferredY = r.bottom + 8;
                                            this.y = Math.max(margin, Math.min(window.innerHeight - menuH - margin, preferredY));
                                        },
                                        toggleAdvanced() {
                                            this.advanced = !this.advanced;
                                            this.$nextTick(() => this.positionMenu());
                                        },
                                    }" x-on:keydown.escape.window="close()" x-on:scroll.window="close()"
                            x-on:resize.window="open && positionMenu()">
                            <button type="button"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 text-base font-semibold leading-none text-gray-700 hover:bg-gray-50 dark:border-white/20 dark:text-gray-200 dark:hover:bg-white/5"
                                x-on:click="toggle($event)">
                                ⋮
                            </button>
                            <template x-teleport="body">
                                <div x-show="open" x-cloak class="fixed inset-0 z-40" x-on:click="close()">
                                    <div x-ref="menu"
                                        class="fixed z-50 w-[min(16rem,calc(100vw-1rem))] max-h-[calc(100vh-1rem)] overflow-y-auto overflow-x-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl dark:border-white/10 dark:bg-gray-900"
                                        :style="`left:${x}px;top:${y}px;`" x-on:click.stop x-transition.origin.top.right>
                                        <button type="button"
                                            class="flex w-full items-center px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                                            x-on:click="close()" wire:click="openDetails('topics', {{ $topic->id }})">
                                            {{ __('models.trash.actions.view') }}
                                        </button>
                                        <button type="button"
                                            class="flex w-full items-center px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                                            x-on:click="close()" wire:click="restoreTopic({{ $topic->id }})">
                                            {{ __('models.trash.actions.restore') }}
                                        </button>
                                        <button type="button"
                                            class="flex w-full items-center px-3 py-2 text-left text-sm text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-500/10"
                                            x-show="!advanced" x-on:click="close()"
                                            wire:click="forceDeleteTopicOnly({{ $topic->id }})"
                                            wire:confirm="{{ __('models.trash.confirmations.force_delete') }}">
                                            {{ __('models.trash.actions.force_delete_topic_only') }}
                                        </button>
                                        <button type="button" x-show="advanced"
                                            class="flex w-full items-center px-3 py-2 text-left text-sm text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-500/10"
                                            x-on:click="close()" wire:click="forceDeleteTopicWithThread({{ $topic->id }})"
                                            wire:confirm="{{ __('models.trash.confirmations.force_delete') }}">
                                            {{ __('models.trash.actions.force_delete_topic_with_thread') }}
                                        </button>
                                        <div class="my-1 border-t border-gray-200 dark:border-white/10"></div>
                                        <button type="button"
                                            class="flex w-full items-center px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5"
                                            x-on:click="toggleAdvanced()">
                                            <span x-show="!advanced">{{ __('models.trash.actions.advanced') }}</span>
                                            <span x-show="advanced">{{ __('models.trash.actions.basic') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                        {{ __('models.trash.empty') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div>
    {{ $this->deletedTopics->links() }}
</div>