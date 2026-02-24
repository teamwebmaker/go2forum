@php
    use Illuminate\Support\Facades\Auth;

    $currUser = Auth::user();

    $isAuthorized = Auth::check();
    $isVerified = $currUser?->isVerified() ?? false;
    $isCategoryVisible = $category?->visibility ?? true;
    $isBlocked = $currUser?->is_blocked ?? false;

    $mine = $mine ?? false;
    $scope = $scope ?? 'all';
    $canFilterMine = $isAuthorized;

    // Topic open state (blocked users should NOT be able to open)
    $topicDisabledMessage = match (true) {
        ! $isAuthorized      => 'ფუნქციის გამოსაყენებლად საჭიროა ავტორიზაცია',
        ! $isVerified        => 'ფუნქციის გამოსაყენებლად საჭიროა ვერიფიკაცია',
        ! $isCategoryVisible => 'კატეგორია დამალულია',
        $isBlocked           => 'თქვენი ანგარიში დროებით შეზღუდულია',
        default              => null,
    };

    $canOpenTopic = $topicDisabledMessage === null;
@endphp

<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

    {{-- Topic Button --}}
    @if ($canOpenTopic)
        <button type="button" data-modal-open="topic-create-modal" @class([
            'inline-flex max-w-max items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition bg-primary-500 hover:bg-primary-600',
        ])>
            თემის გახსნა
            <x-app-icon name="plus" class="h-4 w-4" />
        </button>
    @else
        <x-ui.tooltip
            text="{{ $topicDisabledMessage }}"
            position="top"
            titleClasses="text-amber-600!"
        >
            <span
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm bg-slate-300 cursor-not-allowed"
                role="button"
                aria-disabled="true"
            >
                თემის გახსნა
                <x-app-icon name="plus" class="h-4 w-4" />
            </span>
        </x-ui.tooltip>
    @endif

    <form method="GET" class="w-full sm:w-72 space-y-2" data-search-bar data-search-name="search">

        <div class="flex gap-2 flex-col xs:flex-row">

            {{-- Search --}}
            <div class="relative">
                <x-form.input name="search" :value="$search" placeholder="ძიება" iconPosition="left"
                    inputClass="rounded-lg! pr-10" iconPadding="pl-10" aria-label="თემის ძიება" :displayError="false">
                    <x-slot name="icon">
                        <x-app-icon name="magnifying-glass" class="h-4 w-4 text-slate-400" />
                    </x-slot>
                </x-form.input>
                <button type="button"
                    class="clear-search absolute right-2 top-1/2 -translate-y-1/2 cursor-pointer rounded-full p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    aria-label="ძიების გასუფთავება">
                    <x-app-icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>

            {{-- Filter Select --}}
            <div class="flex items-center gap-2 text-sm text-slate-700 w-full xs:w-30">
                <label class="flex w-full flex-col gap-1">
                    <div class="relative">
                        <select name="scope"
                            class="w-full rounded-lg  border border-gray-300 bg-white py-2 pl-3 text-sm text-slate-800 shadow-sm"
                            onchange="this.form.submit()">
                            <option value="all" {{ $scope === 'all' ? 'selected' : '' }}>ყველა</option>
                            <option value="mine"
                                {{ $scope === 'mine' ? 'selected' : '' }}
                                {{ $canFilterMine ? '' : 'disabled' }}
                            >
                                ჩემი შექმნილი
                            </option>
                        </select>
                    </div>
                </label>
            </div>

        </div>

    </form>
</div>
