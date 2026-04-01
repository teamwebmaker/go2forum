<x-filament.trash.tab-toolbar
    :selected-count="count($selectedMessages)"
    search-id="trash-messages-search"
    search-model="messagesSearch"
    :search-placeholder="__('models.trash.search.messages_placeholder')"
>
    <x-filament.trash.floating-menu
        :button-label="__('models.trash.actions.bulk_actions')"
        button-size="xs"
        button-color="gray"
        menu-width="20rem"
        :fallback-menu-width="320"
        :fallback-menu-height="180"
        align="left"
    >
        <button
            type="button"
            class="flex w-full items-center px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:text-gray-200 dark:hover:bg-white/5"
            wire:click="restoreSelectedMessages"
            x-on:click="close()"
            @disabled(count($selectedMessages) === 0)
        >
            {{ __('models.trash.actions.restore_selected') }}
        </button>

        <button
            type="button"
            class="flex w-full items-center px-3 py-2 text-left text-sm text-danger-600 hover:bg-danger-50 disabled:cursor-not-allowed disabled:opacity-50 dark:text-danger-400 dark:hover:bg-danger-500/10"
            wire:click="forceDeleteSelectedMessages"
            x-on:click="close()"
            wire:confirm="{{ __('models.trash.confirmations.force_delete') }}"
            @disabled(count($selectedMessages) === 0)
        >
            {{ __('models.trash.actions.force_delete_selected') }}
        </button>
    </x-filament.trash.floating-menu>
</x-filament.trash.tab-toolbar>

<div class="overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50 dark:bg-white/5">
            <tr class="text-left">
                <th class="px-4 py-3">
                    <input
                        type="checkbox"
                        wire:model.live="selectAllMessages"
                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                    >
                </th>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">{{ __('models.messages.fields.sender_id') }}</th>
                <th class="px-4 py-3">{{ __('models.messages.fields.content') }}</th>
                <th class="px-4 py-3">{{ __('models.trash.actions.label') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->deletedMessages as $message)
                <tr class="border-t border-gray-100 dark:border-white/10">
                    <td class="px-4 py-3">
                        <input
                            type="checkbox"
                            wire:model.live="selectedMessages"
                            value="{{ $message->id }}"
                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                        >
                    </td>
                    <td class="px-4 py-3">{{ $message->id }}</td>
                    <td class="px-4 py-3">{{ $message->sender?->full_name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        {{ \Illuminate\Support\Str::limit((string) ($message->content ?? ''), 80) }}
                    </td>
                    <td class="px-4 py-3">
                        <x-filament.trash.floating-menu
                            icon-trigger
                            menu-width="11rem"
                            :fallback-menu-width="176"
                            :fallback-menu-height="220"
                            align="right"
                        >
                            <button
                                type="button"
                                class="flex w-full items-center px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                                x-on:click="close()"
                                wire:click="openDetails('messages', {{ $message->id }})"
                            >
                                {{ __('models.trash.actions.view') }}
                            </button>

                            <button
                                type="button"
                                class="flex w-full items-center px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                                x-on:click="close()"
                                wire:click="restoreMessage({{ $message->id }})"
                            >
                                {{ __('models.trash.actions.restore') }}
                            </button>

                            <button
                                type="button"
                                class="flex w-full items-center px-3 py-2 text-left text-sm text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-500/10"
                                x-on:click="close()"
                                wire:click="forceDeleteMessage({{ $message->id }})"
                                wire:confirm="{{ __('models.trash.confirmations.force_delete') }}"
                            >
                                {{ __('models.trash.actions.force_delete') }}
                            </button>
                        </x-filament.trash.floating-menu>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                        {{ __('models.trash.empty') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<x-filament.trash.pagination :paginator="$this->deletedMessages" />
