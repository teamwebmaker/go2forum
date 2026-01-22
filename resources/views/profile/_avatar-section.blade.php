@props([
    'user',
    'avatarUrl' => null,
    'avatarInitial' => '?',
    'isEditing' => false,
    'isVerified' => false,
])

<div class="flex flex-col gap-4 sm:flex-row sm:items-start">
    <div class="h-20 w-20 overflow-hidden rounded-full border border-slate-200 bg-slate-100">
        @if ($avatarUrl)
            <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="h-full w-full object-cover" />
        @else
            <div class="flex h-full w-full items-center justify-center text-xl font-semibold text-slate-600">
                {{ $avatarInitial }}
            </div>
        @endif
    </div>

    @if ($isEditing)
        <div class="flex-1 space-y-2">
            @if ($isVerified)
                <label
                    class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 cursor-pointer">
                    <input type="file" name="image" id="profile-image-input" accept="image/png,image/jpeg,image/webp"
                        class="sr-only" @disabled(!$isVerified) data-avatar-input>
                    აირჩიე ახალი ფოტო
                </label>
            @endif

            <p class="text-xs text-slate-500 max-w-sm">
                {{ $isVerified ? 'PNG, JPG, WEBP, მაქს 2MB.' : 'ფოტოს განახლება/ატვირთვა ხელმისაწვდომია მხოლოდ ვერიფიცირებული მომხმარებლებისთვის.' }}
            </p>

            @if ($avatarUrl)
                <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700">
                    <input type="checkbox" name="remove_image" value="1"
                        class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                    მიმდინარე ფოტოს წაშლა
                </label>
            @endif

            {{-- Selected image preview --}}
            <div class="hidden items-center gap-3 rounded-xl bg-slate-50/80 max-w-md px-3 py-2 text-sm text-slate-700 ring-1 ring-slate-200/70"
                data-avatar-preview>
                <div class="h-12 w-12 overflow-hidden rounded-full bg-white shadow-inner ring-1 ring-slate-200">
                    <img data-avatar-preview-image alt="Preview" class="h-full w-full object-cover" />
                </div>
                <div class="leading-tight">
                    <p class="text-sm font-semibold text-slate-800">ახალი სურათი</p>
                    <p class="text-xs text-slate-500" data-avatar-preview-name></p>
                </div>
            </div>
        </div>
    @endif
</div>
