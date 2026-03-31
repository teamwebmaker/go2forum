<div class="fixed inset-0 z-50" wire:keydown.escape.window="closeDetailsModal">
    <div class="absolute inset-0 bg-black/50" wire:click="closeDetailsModal"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-3xl rounded-xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('models.trash.actions.view') }} · {{ $detailsTitle }}
                </h3>
                <button
                    type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 dark:border-white/20 dark:text-gray-200 dark:hover:bg-white/5"
                    wire:click="closeDetailsModal"
                >
                    ×
                </button>
            </div>

            <div class="max-h-[70vh] overflow-y-auto px-4 py-4">
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($detailsRows as $row)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                {{ $row['label'] ?? '-' }}
                            </p>
                            <p class="mt-1 break-words text-sm text-gray-900 dark:text-gray-100">
                                {{ $row['value'] ?? '-' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end border-t border-gray-200 px-4 py-3 dark:border-white/10">
                <x-filament::button color="gray" wire:click="closeDetailsModal">
                    {{ __('models.trash.actions.close') }}
                </x-filament::button>
            </div>
        </div>
    </div>
</div>
