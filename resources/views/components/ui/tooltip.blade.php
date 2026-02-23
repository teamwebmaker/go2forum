@props([
    'text',
    'position' => 'top',
    'size' => 'auto', // auto|xs|sm|md
    'triggerClass' => '',
    'titleClasses' => '',
])

@php
    $slotHtml = trim((string) $slot);
    $usesMobileQuestionTrigger = $slotHtml !== '' && strip_tags($slotHtml) === $slotHtml;

    $sizeClasses = match ($size) {
        'xs' => 'w-auto sm:w-40 max-w-[calc(100vw-2rem)]',
        'sm' => 'w-auto sm:w-64 max-w-[calc(100vw-2rem)]',
        'md' => 'w-auto sm:w-80 max-w-[calc(100vw-2rem)]',
        default => 'w-auto sm:w-max max-w-[calc(100vw-2rem)] sm:max-w-[16rem]',
    };
    $tooltipId = 'tooltip-'.\Illuminate\Support\Str::ulid();
@endphp

<span
    class="relative inline-flex"
    x-data="{
        open: false,
        pinned: false,
        useQuestionTriggerOnTouch: @js($usesMobileQuestionTrigger),
        preferredPlacement: @js($position),
        placement: @js($position),
        tooltipStyle: '',
        lastTouchAt: 0,
        isCoarsePointer() {
            return window.matchMedia('(hover: none), (pointer: coarse)').matches;
        },
        markTouch() {
            this.lastTouchAt = Date.now();
        },
        isTouchInteraction() {
            return this.isCoarsePointer() || (Date.now() - this.lastTouchAt) < 1200;
        },
        shouldUseQuestionTrigger() {
            return this.useQuestionTriggerOnTouch && this.isCoarsePointer();
        },
        show() {
            this.open = true;
            this.$nextTick(() => this.updatePosition());
        },
        hide() {
            if (this.pinned) return;
            this.open = false;
        },
        close() {
            this.open = false;
            this.pinned = false;
        },
        toggleTouch() {
            if (!this.isTouchInteraction()) return;

            if (this.open && this.pinned) {
                this.close();
                return;
            }

            this.pinned = true;
            this.open = true;
            this.$nextTick(() => this.updatePosition());
        },
        onTriggerClick() {
            if (this.shouldUseQuestionTrigger() && this.isTouchInteraction()) {
                return;
            }

            this.toggleTouch();
        },
        onFocusOut(event) {
            if (this.pinned) return;
            if (event.relatedTarget && this.$el.contains(event.relatedTarget)) return;
            this.open = false;
        },
        onViewportChange() {
            if (!this.open) return;
            this.$nextTick(() => this.updatePosition());
        },
        candidateOrder(preferred) {
            const opposite = {
                top: 'bottom',
                bottom: 'top',
                left: 'right',
                right: 'left',
            };

            return [...new Set([preferred, opposite[preferred], 'top', 'bottom', 'right', 'left'])];
        },
        coordsFor(placement, triggerRect, tooltipRect, gap) {
            const centerX = triggerRect.left + (triggerRect.width / 2) - (tooltipRect.width / 2);
            const centerY = triggerRect.top + (triggerRect.height / 2) - (tooltipRect.height / 2);

            if (placement === 'bottom') {
                return { x: centerX, y: triggerRect.bottom + gap };
            }

            if (placement === 'left') {
                return { x: triggerRect.left - tooltipRect.width - gap, y: centerY };
            }

            if (placement === 'right') {
                return { x: triggerRect.right + gap, y: centerY };
            }

            return { x: centerX, y: triggerRect.top - tooltipRect.height - gap };
        },
        overflowScore(coords, tooltipRect, viewportWidth, viewportHeight, margin) {
            const overflowLeft = Math.max(0, margin - coords.x);
            const overflowRight = Math.max(0, (coords.x + tooltipRect.width + margin) - viewportWidth);
            const overflowTop = Math.max(0, margin - coords.y);
            const overflowBottom = Math.max(0, (coords.y + tooltipRect.height + margin) - viewportHeight);

            return (overflowTop + overflowBottom) * 2 + overflowLeft + overflowRight;
        },
        clamp(value, min, max) {
            if (max < min) return min;
            return Math.min(Math.max(value, min), max);
        },
        updatePosition() {
            const trigger = (this.shouldUseQuestionTrigger() && this.$refs.mobileTrigger)
                ? this.$refs.mobileTrigger
                : this.$refs.trigger;
            const tooltip = this.$refs.tooltip;
            if (!trigger || !tooltip || !this.open) return;

            const gap = 8;
            const margin = 12;
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const triggerRect = trigger.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();

            let bestPlacement = this.preferredPlacement;
            let bestCoords = this.coordsFor(bestPlacement, triggerRect, tooltipRect, gap);
            let bestScore = this.overflowScore(bestCoords, tooltipRect, viewportWidth, viewportHeight, margin);

            for (const candidate of this.candidateOrder(this.preferredPlacement)) {
                const coords = this.coordsFor(candidate, triggerRect, tooltipRect, gap);
                const score = this.overflowScore(coords, tooltipRect, viewportWidth, viewportHeight, margin);
                if (score < bestScore) {
                    bestScore = score;
                    bestPlacement = candidate;
                    bestCoords = coords;
                }
                if (score === 0) {
                    bestPlacement = candidate;
                    bestCoords = coords;
                    break;
                }
            }

            const maxLeft = viewportWidth - tooltipRect.width - margin;
            const maxTop = viewportHeight - tooltipRect.height - margin;
            const left = this.clamp(bestCoords.x, margin, maxLeft);
            const top = this.clamp(bestCoords.y, margin, maxTop);

            this.placement = bestPlacement;
            this.tooltipStyle = `left:${left}px; top:${top}px;`;
        },
    }"
    @mouseenter="if (!isTouchInteraction()) show()"
    @mouseleave="if (!isTouchInteraction()) hide()"
    @focusin="show()"
    @focusout="onFocusOut($event)"
    @keydown.escape.window="if (open) close()"
    @resize.window="onViewportChange()"
    @scroll.window.passive="onViewportChange()"
    @click.window="
        if (!open || !pinned) return;
        if ($refs.trigger?.contains($event.target)) return;
        if ($refs.mobileTrigger?.contains($event.target)) return;
        close();
    "
>
    <span
        x-ref="trigger"
        class="{{ $triggerClass }} inline-flex items-center"
        @touchstart.passive="markTouch()"
        @pointerdown="if (['touch', 'pen'].includes($event.pointerType)) markTouch()"
        @click="onTriggerClick()"
        x-bind:aria-describedby="open ? @js($tooltipId) : null"
    >
        {{ $slot }}
    </span>

    @if ($usesMobileQuestionTrigger)
        <button
            type="button"
            x-ref="mobileTrigger"
            x-cloak
            x-show="shouldUseQuestionTrigger()"
            class="ml-1 inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-300 bg-white text-[11px] font-semibold leading-none text-slate-600 shadow-sm transition hover:border-slate-400 hover:text-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
            @touchstart.passive="markTouch()"
            @pointerdown="if (['touch', 'pen'].includes($event.pointerType)) markTouch()"
            @click.prevent.stop="toggleTouch()"
            x-bind:aria-expanded="open ? 'true' : 'false'"
            x-bind:aria-describedby="open ? @js($tooltipId) : null"
            aria-label="ინფორმაცია"
        >?</button>
    @endif

    <template x-teleport="body">
        <span
            x-ref="tooltip"
            id="{{ $tooltipId }}"
            role="tooltip"
            x-cloak
            x-show="open"
            x-transition.opacity.duration.120ms
            x-bind:data-placement="placement"
            x-bind:style="tooltipStyle"
            class="fixed z-70 pointer-events-none {{ $sizeClasses }} {{ $titleClasses }}
                rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-left text-sm leading-5 text-slate-700 shadow-lg
                whitespace-normal wrap-break-words sm:rounded-md sm:px-3 sm:py-2 sm:text-xs sm:shadow-md"
        >
            {{ $text }}
        </span>
    </template>
</span>
