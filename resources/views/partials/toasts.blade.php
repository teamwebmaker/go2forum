<div data-toast-container class="fixed left-1/2 top-4 z-50 -translate-x-1/2 w-full sm:w-auto flex flex-col items-center space-y-2">
    @foreach (['success', 'error', 'warning', 'info'] as $type)
        @if (session($type))
            <x-ui.toast :type="$type" :messages="session($type)" />
        @endif
    @endforeach

    @if ($errors->any())
        <x-ui.toast type="error" :messages="$errors->all()" />
    @endif
</div>
