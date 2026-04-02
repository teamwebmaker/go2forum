@props([
    'selectedCount' => 0,
    'searchId' => 'trash-search',
    'searchModel' => '',
    'searchPlaceholder' => '',
])

<div class="flex flex-wrap items-center justify-between gap-2">
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-sm text-gray-600 dark:text-gray-300">
            {{ $selectedCount }} არჩეული
        </span>

        {{ $slot }}
    </div>

    <div class="sm:ml-auto sm:w-auto">
        <input
            id="{{ $searchId }}"
            type="search"
            wire:model.live.debounce.350ms="{{ $searchModel }}"
            placeholder="{{ $searchPlaceholder }}"
            class="h-10 w-full rounded-lg border-gray-300 px-3.5 py-2 text-sm shadow-sm focus:border-primary-500    dark:border-white/20 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-400"
        >
    </div>
</div>
