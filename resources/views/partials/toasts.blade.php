@foreach (['success', 'error', 'warning', 'info'] as $type)
    @if (session($type))
        <x-ui.toast :type="$type" :messages="[session($type)]" />
    @endif
@endforeach

@if ($errors->any())
    <x-ui.toast :type="$type" :title="$title" :messages="$errors->all()"" />
@endif