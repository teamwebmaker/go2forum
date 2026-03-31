<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex flex-wrap gap-2">
            <x-filament::button wire:click="setTab('users')" :color="$activeTab === 'users' ? 'primary' : 'gray'"
                size="sm">
                {{ __('models.trash.tabs.users') }}
            </x-filament::button>

            <x-filament::button wire:click="setTab('topics')" :color="$activeTab === 'topics' ? 'primary' : 'gray'"
                size="sm">
                {{ __('models.trash.tabs.topics') }}
            </x-filament::button>

            <x-filament::button wire:click="setTab('messages')" :color="$activeTab === 'messages' ? 'primary' : 'gray'"
                size="sm">
                {{ __('models.trash.tabs.messages') }}
            </x-filament::button>
        </div>

        @if ($activeTab === 'users')
            @include('filament.pages.trash.partials.users-tab')
        @endif

        @if ($activeTab === 'topics')
            @include('filament.pages.trash.partials.topics-tab')
        @endif

        @if ($activeTab === 'messages')
            @include('filament.pages.trash.partials.messages-tab')
        @endif

        @if ($showDetailsModal)
            @include('filament.pages.trash.partials.details-modal')
        @endif
    </div>
</x-filament-panels::page>
