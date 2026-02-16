<div x-data="{
        search: '',
        participants: @js($participants),
        filteredCount() {
            const term = this.search.trim().toLowerCase();

            if (! term.length) {
                return this.participants.length;
            }

            return this.participants.filter((participant) => (participant.name ?? '').toLowerCase().includes(term)).length;
        },
    }" class="space-y-4">
    {{-- Search --}}
    <div class="space-y-1">
        <label class="text-sm font-medium text-gray-950 dark:text-white">
            {{ __('models.conversations.actions.participants.search_label') }}
        </label>

        <input x-model.debounce.300ms="search" type="text"
            placeholder="{{ __('models.conversations.actions.participants.search_placeholder') }}"
            aria-label="{{ __('models.conversations.actions.participants.search_aria') }}"
            class="block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-950 shadow-sm outline-none ring-primary-500 transition focus:border-primary-500 focus:ring-2 dark:border-white/15 dark:bg-gray-900 dark:text-white dark:focus:border-primary-400 dark:focus:ring-primary-400" />
    </div>

    {{-- Total and filtered count Results --}}
    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
        <span>{{ __('models.conversations.actions.participants.total') }}: <span
                x-text="participants.length"></span></span>
        <span>{{ __('models.conversations.actions.participants.results') }}: <span
                x-text="filteredCount()"></span></span>
    </div>

    {{-- Participants list --}}
    <div class="max-h-96 space-y-2 overflow-y-auto pr-1">
        @foreach ($participants as $participant)
            @php
                $name = $participant['name'] ?? '-';
                $joinedAt = $participant['joined_at'] ?? '-';
            @endphp

            <div class="flex items-center justify-between rounded-xl border border-gray-200 px-3 py-2 dark:border-white/10"
                data-name="{{ mb_strtolower($name) }}"
                x-show="! search.trim().length || $el.dataset.name.includes(search.trim().toLowerCase())">
                <p class="text-sm font-medium text-gray-950 dark:text-white">
                    {{ $name }}
                </p>
            </div>
        @endforeach

        {{-- No results --}}
        <p x-show="filteredCount() === 0" x-cloak
            class="rounded-xl border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500 dark:border-white/20 dark:text-gray-400">
            {{ __('models.conversations.actions.participants.empty') }}
        </p>
    </div>
</div>