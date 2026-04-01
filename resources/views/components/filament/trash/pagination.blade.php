@props([
    'paginator',
])

@if ($paginator->hasPages())
    <div class="fi-trash-pagination mt-2">
        <x-filament::pagination
            :paginator="$paginator"
            class="w-full justify-between gap-3"
        />
    </div>
@endif
