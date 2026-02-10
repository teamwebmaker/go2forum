<div data-livewire-upload data-upload-id="{{ $inputId }}" class="w-full space-y-3">
    {{-- Label --}}
    @if (!empty($label) || !empty($helpText))
        <div class="space-y-1">
            @if (!empty($label))
                <label for="{{ $inputId }}" class="block text-sm font-semibold text-slate-900">
                    {{ $label }}
                </label>
            @endif

            @if (!empty($helpText))
                <p class="text-xs leading-5 text-slate-600">
                    {{ $helpText }}
                </p>
            @endif
        </div>
    @endif

    {{-- Upload --}}
    <div data-upload-drop data-dragging="false" class="relative rounded-2xl border border-dashed border-slate-300 bg-white p-4 shadow-sm transition
               hover:border-slate-400 hover:bg-slate-50/40
               focus-within:ring-2 focus-within:ring-primary-200 focus-within:border-primary-300
               data-[dragging=true]:border-primary-400 data-[dragging=true]:bg-primary-50/50 data-[dragging=true]:ring-2 data-[dragging=true]:ring-primary-200
               {{ $disabled ? 'pointer-events-none opacity-60' : '' }}" role="group"
        aria-disabled="{{ $disabled ? 'true' : 'false' }}">
        <input id="{{ $inputId }}" type="file" data-upload-input class="sr-only"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}" wire:model="value" accept="{{ $accept }}" {{ $multiple ? 'multiple' : '' }} {{ $disabled ? 'disabled' : '' }} />

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <div
                    class="shrink-0 mt-0.5 flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700 ring-1 ring-slate-200">
                    <x-app-icon name="arrow-up-tray" class="size-5" />
                </div>

                <div class="space-y-1">
                    <p class="text-sm font-semibold text-slate-900">
                        ჩააგდეთ ფაილები აქ ან <label for="{{ $inputId }}"
                            class="cursor-pointer text-primary-600 underline">
                            აარჩიეთ
                        </label>
                    </p>
                    <p class="text-xs text-slate-600">
                        {{ $multiple ? 'შეგიძლიათ ატვირთოთ რამდენიმე ფაილი (სურათები და დოკუმენტები).' : 'ატვირთეთ ერთი ფაილი (სურათი ან დოკუმენტი).' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-4 hidden" data-upload-progress aria-live="polite">
            <div class="flex items-center justify-between gap-3">
                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 ring-1 ring-slate-200">
                    <div class="h-full w-0 rounded-full bg-primary-600 transition-all" data-upload-progress-bar></div>
                </div>
                <div class="min-w-12 text-right text-xs font-medium tabular-nums text-slate-600"
                    data-upload-progress-text>
                    0%
                </div>
            </div>
        </div>

        <div class="pointer-events-none absolute inset-0 hidden items-center justify-center rounded-2xl bg-primary-50/70 text-sm font-semibold text-primary-800 ring-2 ring-primary-200"
            data-upload-overlay>
            გაუშვით ფაილები ატვირთვისთვის
        </div>
    </div>

    {{-- error --}}
    @if ($errors->has('value') || $errors->has('value.*'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
            <div class="flex items-start gap-2">
                <x-app-icon name="exclamation-triangle" class="mt-0.5 size-4 text-rose-600" />
                <p class="text-xs leading-5">
                    {{ $errors->first('value') ?: $errors->first('value.*') }}
                </p>
            </div>
        </div>
    @endif

    {{-- images --}}
    @if (!empty($imageItems))
        <div class="space-y-2">
            <p class="text-xs font-semibold text-slate-700">სურათები</p>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($imageItems as $item)
                    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        @if ($item['preview_url'])
                            <img src="{{ $item['preview_url'] }}" alt="{{ $item['name'] }}" class="h-28 w-full object-cover" />
                        @else
                            <div class="flex h-28 items-center justify-center text-slate-400">
                                <x-app-icon name="photo" class="size-6" />
                            </div>
                        @endif

                        <div class="flex items-center justify-between gap-2 border-t border-slate-100 bg-white/90 px-3 py-2">
                            <p class="truncate text-xs font-medium text-slate-700">
                                {{ $item['name'] }}
                            </p>

                            <button type="button" wire:click="removeFile({{ $item['index'] }})"
                                class="inline-flex size-8 items-center justify-center rounded-xl text-slate-600 transition hover:bg-rose-50 hover:text-rose-700"
                                aria-label="სურათის წაშლა">
                                <x-app-icon name="x-mark" class="size-4" />
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- files --}}
    @if (!empty($fileItems))
        <div class="space-y-2">
            <p class="text-xs font-semibold text-slate-700">ფაილები</p>

            <ul class="space-y-2">
                @foreach ($fileItems as $item)
                    <li
                        class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700 ring-1 ring-slate-200">
                                <x-app-icon name="{{ $item['icon'] }}" class="size-5" />
                            </div>

                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">
                                    {{ $item['name'] }}
                                </p>
                                <p class="text-xs text-slate-600">
                                    {{ strtoupper($item['extension'] ?: 'FILE') }} • {{ $item['size'] }}
                                </p>
                            </div>
                        </div>

                        <button type="button" wire:click="removeFile({{ $item['index'] }})"
                            class="inline-flex size-9 items-center justify-center rounded-xl text-slate-600 transition hover:bg-rose-50 hover:text-rose-700"
                            aria-label="ფაილის წაშლა">
                            <x-app-icon name="x-mark" class="size-4" />
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        (() => {
            if (window.__livewireUploadFieldInit) return;
            window.__livewireUploadFieldInit = true;

            const getWrapperFromEvent = (event) => {
                // Livewire upload events can originate from the input or a child.
                // Try multiple ways to find the wrapper reliably.
                return (
                    event?.target?.closest?.('[data-livewire-upload]') ||
                    event?.detail?.target?.closest?.('[data-livewire-upload]') ||
                    null
                );
            };

            const getDrop = (el) => el?.closest?.('[data-upload-drop]');

            const setProgress = (wrapper, progress, show) => {
                const bar = wrapper.querySelector('[data-upload-progress-bar]');
                const meter = wrapper.querySelector('[data-upload-progress]');
                const label = wrapper.querySelector('[data-upload-progress-text]');
                if (!bar || !meter || !label) return;

                if (show) {
                    meter.classList.remove('hidden');
                    bar.style.width = `${progress}%`;
                    label.textContent = `${progress}%`;
                    return;
                }

                bar.style.width = '0%';
                meter.classList.add('hidden');
                label.textContent = '0%';
            };

            const setDragging = (drop, isDragging) => {
                if (!drop) return;
                drop.dataset.dragging = isDragging ? 'true' : 'false';

                const overlay = drop.querySelector('[data-upload-overlay]');
                if (!overlay) return;

                if (isDragging) overlay.classList.remove('hidden');
                else overlay.classList.add('hidden');
            };

            document.addEventListener('livewire-upload-start', (event) => {
                const wrapper = getWrapperFromEvent(event);
                if (!wrapper) return;
                setProgress(wrapper, 0, true);
            });

            document.addEventListener('livewire-upload-progress', (event) => {
                const wrapper = getWrapperFromEvent(event);
                if (!wrapper) return;
                setProgress(wrapper, event.detail.progress || 0, true);
            });

            document.addEventListener('livewire-upload-finish', (event) => {
                const wrapper = getWrapperFromEvent(event);
                if (!wrapper) return;
                setProgress(wrapper, 100, false);
            });

            document.addEventListener('livewire-upload-error', (event) => {
                const wrapper = getWrapperFromEvent(event);
                if (!wrapper) return;
                setProgress(wrapper, 0, false);
            });

            document.addEventListener('dragover', (event) => {
                const drop = getDrop(event.target);
                if (!drop) return;
                event.preventDefault();
                setDragging(drop, true);
            });

            document.addEventListener('dragleave', (event) => {
                const drop = getDrop(event.target);
                if (!drop) return;
                if (event.relatedTarget && drop.contains(event.relatedTarget)) return;
                setDragging(drop, false);
            });

            document.addEventListener('drop', (event) => {
                const drop = getDrop(event.target);
                if (!drop) return;
                event.preventDefault();
                setDragging(drop, false);

                const input = drop.querySelector('[data-upload-input]');
                if (!input || !event.dataTransfer?.files?.length) return;

                const dataTransfer = new DataTransfer();
                if (input.multiple) {
                    Array.from(event.dataTransfer.files).forEach((file) => dataTransfer.items.add(file));
                } else {
                    dataTransfer.items.add(event.dataTransfer.files[0]);
                }

                input.files = dataTransfer.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        })();
    </script>

@endpush