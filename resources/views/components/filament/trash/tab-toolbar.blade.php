@props([
    'selectedCount' => 0,
    'searchId' => 'trash-search',
    'searchModel' => '',
    'searchPlaceholder' => '',
])

<div class="flex flex-wrap  items-center justify-between gap-2">
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-sm text-gray-600 dark:text-gray-300">
            {{ $selectedCount }} არჩეული
        </span>

        {{ $slot }}
    </div>

    <div class="">
        <label class="sr-only" for="{{ $searchId }}">{{ __('models.trash.search.label') }}</label>
        <input
            id="{{ $searchId }}"
            type="search"
            wire:model.live.debounce.350ms="{{ $searchModel }}"
            placeholder="{{ $searchPlaceholder }}"
            class="w-full rounded-lg border-gray-300 px-3 py-1.5 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:w-72 dark:border-white/20 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-400"
        >
    </div>
</div>
