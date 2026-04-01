<x-filament.trash.tab-toolbar
    :selected-count="count($selectedUsers)"
    search-id="trash-users-search"
    search-model="usersSearch"
    :search-placeholder="__('models.trash.search.users_placeholder')"
>
    <x-filament.trash.floating-menu
        :button-label="__('models.trash.actions.bulk_actions')"
        button-size="xs"
        button-color="gray"
        menu-width="22rem"
        :fallback-menu-width="352"
        :fallback-menu-height="260"
        align="left"
    >
        <button
            type="button"
            class="flex w-full items-center px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:text-gray-200 dark:hover:bg-white/5"
            wire:click="restoreSelectedUsers"
            x-on:click="close()"
            @disabled(count($selectedUsers) === 0)
        >
            {{ __('models.trash.actions.restore_selected') }}
        </button>

        <button
            type="button"
            x-show="!advanced"
            class="flex w-full items-center px-3 py-2 text-left text-sm text-danger-600 hover:bg-danger-50 disabled:cursor-not-allowed disabled:opacity-50 dark:text-danger-400 dark:hover:bg-danger-500/10"
            wire:click="forceDeleteSelectedUsersKeepPublic"
            x-on:click="close()"
            wire:confirm="{{ __('models.trash.confirmations.force_delete') }}"
            @disabled(count($selectedUsers) === 0)
        >
            {{ __('models.trash.actions.force_delete_selected_keep_public') }}
        </button>

        <button
            type="button"
            x-show="advanced"
            class="flex w-full items-center px-3 py-2 text-left text-sm text-danger-600 hover:bg-danger-50 disabled:cursor-not-allowed disabled:opacity-50 dark:text-danger-400 dark:hover:bg-danger-500/10"
            wire:click="forceDeleteSelectedUsersWithPublic"
            x-on:click="close()"
            wire:confirm="{{ __('models.trash.confirmations.force_delete') }}"
            @disabled(count($selectedUsers) === 0)
        >
            {{ __('models.trash.actions.force_delete_selected_with_public') }}
        </button>

        <div class="my-1 border-t border-gray-200 dark:border-white/10"></div>

        <button
            type="button"
            class="flex w-full items-center px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5"
            x-on:click="toggleAdvanced()"
        >
            <span x-show="!advanced">{{ __('models.trash.actions.advanced') }}</span>
            <span x-show="advanced">{{ __('models.trash.actions.basic') }}</span>
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
                        wire:model.live="selectAllUsers"
                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                    >
                </th>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">{{ __('models.users.fields.name') }}</th>
                <th class="px-4 py-3">{{ __('models.users.fields.email') }}</th>
                <th class="px-4 py-3">{{ __('models.users.fields.deleted_at') }}</th>
                <th class="px-4 py-3">{{ __('models.trash.actions.label') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->deletedUsers as $user)
                <tr class="border-t border-gray-100 dark:border-white/10">
                    <td class="px-4 py-3">
                        <input
                            type="checkbox"
                            wire:model.live="selectedUsers"
                            value="{{ $user->id }}"
                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                        >
                    </td>
                    <td class="px-4 py-3">{{ $user->id }}</td>
                    <td class="px-4 py-3">{{ $user->full_name }}</td>
                    <td class="px-4 py-3">{{ $user->email }}</td>
                    <td class="px-4 py-3">{{ $user->deleted_at?->format('Y-m-d H:i') ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <x-filament.trash.floating-menu
                            icon-trigger
                            menu-width="16rem"
                            :fallback-menu-width="256"
                            :fallback-menu-height="280"
                            align="right"
                        >
                            <button
                                type="button"
                                class="flex w-full items-center px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                                x-on:click="close()"
                                wire:click="openDetails('users', {{ $user->id }})"
                            >
                                {{ __('models.trash.actions.view') }}
                            </button>

                            <button
                                type="button"
                                class="flex w-full items-center px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                                x-on:click="close()"
                                wire:click="restoreUser({{ $user->id }})"
                            >
                                {{ __('models.trash.actions.restore') }}
                            </button>

                            <button
                                type="button"
                                x-show="!advanced"
                                class="flex w-full items-center px-3 py-2 text-left text-sm text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-500/10"
                                x-on:click="close()"
                                wire:click="forceDeleteUserKeepPublic({{ $user->id }})"
                                wire:confirm="{{ __('models.trash.confirmations.force_delete') }}"
                            >
                                {{ __('models.trash.actions.force_delete_keep_public') }}
                            </button>

                            <button
                                type="button"
                                x-show="advanced"
                                class="flex w-full items-center px-3 py-2 text-left text-sm text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-500/10"
                                x-on:click="close()"
                                wire:click="forceDeleteUserWithPublic({{ $user->id }})"
                                wire:confirm="{{ __('models.trash.confirmations.force_delete') }}"
                            >
                                {{ __('models.trash.actions.force_delete_with_public') }}
                            </button>

                            <div class="my-1 border-t border-gray-200 dark:border-white/10"></div>

                            <button
                                type="button"
                                class="flex w-full items-center px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5"
                                x-on:click="toggleAdvanced()"
                            >
                                <span x-show="!advanced">{{ __('models.trash.actions.advanced') }}</span>
                                <span x-show="advanced">{{ __('models.trash.actions.basic') }}</span>
                            </button>
                        </x-filament.trash.floating-menu>
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

<x-filament.trash.pagination :paginator="$this->deletedUsers" />
