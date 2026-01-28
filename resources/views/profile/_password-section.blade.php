<section id="user-info-password" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-black/5">
    <header class="flex items-start flex-col sm:flex-row justify-between gap-4">
        <div class="space-y-1">
            <h2 class="text-base font-semibold text-slate-900">პაროლის შეცვლა</h2>
            <p class="text-sm text-slate-600">გაანახლე პაროლი. ცვლილების შემდეგ სხვა მოწყობილობებიდან გასვლა ავტომატურად მოხდება.</p>
        </div>
        <div>
            @if (!$isPasswordEditing)
                <a href="{{ route('profile.user-info', ['password' => 1]) }}"
                    class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400/20">
                    პაროლის შეცვლა
                </a>
            @else
                <a href="{{ route('profile.user-info') }}"
                    class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400/20">
                    დახურვა
                </a>
            @endif
        </div>
    </header>

    @if ($isPasswordEditing && !$isEditing)
        <form method="POST" action="{{ route('profile.password.update') }}" class="mt-6 space-y-4 max-w-xl">
            @csrf
            @method('PATCH')
            <input type="hidden" name="_password_edit" value="1">

            <x-form.input name="current_password" type="password" label="მიმდინარე პაროლი" required
                autocomplete="current-password" minlength="8" />

            <x-form.input name="password" type="password" label="ახალი პაროლი" required autocomplete="new-password"
                minlength="8" />

            <x-form.input name="password_confirmation" type="password" label="გაიმეორე ახალი პაროლი" required
                autocomplete="new-password" minlength="8" />

            <div class="pt-2">
                <x-button type="submit">პაროლის განახლება</x-button>
            </div>
        </form>
    @endif
</section>
