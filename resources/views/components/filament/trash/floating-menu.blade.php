@props([
    'buttonLabel' => null,
    'buttonColor' => 'gray',
    'buttonSize' => 'xs',
    'buttonClass' => '',
    'iconTrigger' => false,
    'menuWidth' => '16rem',
    'fallbackMenuWidth' => 256,
    'fallbackMenuHeight' => 260,
    'align' => 'left',
])

<div
    class="relative inline-block text-left"
    x-data="{
        open: false,
        advanced: false,
        x: 8,
        y: 8,
        triggerEl: null,
        menuWidth: @js($menuWidth),
        fallbackMenuWidth: @js((int) $fallbackMenuWidth),
        fallbackMenuHeight: @js((int) $fallbackMenuHeight),
        align: @js($align),
        close() {
            this.open = false;
            this.advanced = false;
        },
        toggle(event) {
            this.triggerEl = event.currentTarget;
            this.advanced = false;
            this.open = !this.open;
            if (!this.open) return;
            this.$nextTick(() => this.positionMenu());
        },
        positionMenu() {
            const trigger = this.triggerEl;
            const menu = this.$refs.menu;
            if (!trigger || !menu) return;

            const margin = 8;
            const r = trigger.getBoundingClientRect();
            const menuW = menu.offsetWidth || this.fallbackMenuWidth;
            const menuH = menu.offsetHeight || this.fallbackMenuHeight;

            if (this.align === 'right') {
                this.x = Math.max(margin, Math.min(window.innerWidth - menuW - margin, r.right - menuW));
            } else {
                this.x = Math.max(margin, Math.min(window.innerWidth - menuW - margin, r.left));
            }

            const preferredY = r.bottom + 8;
            this.y = Math.max(margin, Math.min(window.innerHeight - menuH - margin, preferredY));
        },
        toggleAdvanced() {
            this.advanced = !this.advanced;
            this.$nextTick(() => this.positionMenu());
        },
    }"
    x-on:keydown.escape.window="close()"
    x-on:scroll.window="close()"
    x-on:resize.window="open && positionMenu()"
>
    @if ($iconTrigger)
        <button
            type="button"
            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 text-base font-semibold leading-none text-gray-700 hover:bg-gray-50 dark:border-white/20 dark:text-gray-200 dark:hover:bg-white/5"
            x-on:click="toggle($event)"
        >
            ⋮
        </button>
    @else
        <x-filament::button
            size="{{ $buttonSize }}"
            color="{{ $buttonColor }}"
            class="{{ $buttonClass }}"
            x-on:click="toggle($event)"
        >
            {{ $buttonLabel }}
        </x-filament::button>
    @endif

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-40" x-on:click="close()">
            <div
                x-ref="menu"
                class="fixed z-50 max-h-[calc(100vh-1rem)] overflow-y-auto overflow-x-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl dark:border-white/10 dark:bg-gray-900"
                :style="`left:${x}px;top:${y}px;width:min(${menuWidth}, calc(100vw - 1rem));`"
                x-on:click.stop
                x-transition.origin.top.left
            >
                {{ $slot }}
            </div>
        </div>
    </template>
</div>
