<section class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-black/5"
    aria-labelledby="profile-basic-info-title">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-slate-50/80 to-transparent"></div>

    <div class="relative">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 space-y-1">
                <div class="flex items-start gap-3">
                    <span
                        class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-700">
                        <x-app-icon name="user" class="h-5 w-5" />
                    </span>

                    <div class="min-w-0">
                        <h2 id="profile-basic-info-title" class="text-lg font-semibold text-slate-900">ძირითადი ინფორმაცია</h2>
                        <p class="text-sm text-slate-600">ნახე და განაახლე პირადი დეტალები.</p>
                    </div>
                </div>

                <div
                    class="mt-2 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs text-slate-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    <span>ბოლოს განახლდა: {{ $user->updated_at->locale('ka')->translatedFormat('d M Y') }}</span>
                </div>
            </div>

            @unless ($isEditing)
                <a href="{{ route('profile.user-info', ['edit' => 1]) }}"
                    class="inline-flex items-center gap-2 rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400/20">
                    <x-app-icon name="pencil-square" class="h-4 w-4" />
                    რედაქტირება
                </a>
            @endunless
        </header>

        <div class="mt-5 border-t border-slate-200"></div>

        @if (!$isEditing)
            <div class="mt-6 space-y-5">
                <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
                    <div class="grid gap-4 lg:grid-cols-[auto,1fr] lg:items-start">
                        @include('profile._avatar-section', [
                            'user' => $user,
                            'isEditing' => false,
                            'isVerified' => $isVerified,
                        ])

                        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div class="rounded-lg border border-slate-200/70 bg-slate-50 p-4">
                                <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">სახელი</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $user->name }}</dd>
                            </div>

                            <div class="rounded-lg border border-slate-200/70 bg-slate-50 p-4">
                                <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">გვარი</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $user->surname }}</dd>
                            </div>

                            <div class="rounded-lg border border-slate-200/70 bg-slate-50 p-4">
                                <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">ზედმეტსახელი</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $user->nickname ?: '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-slate-200/70 bg-slate-50 p-4">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">ელ.ფოსტა</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900 break-all">{{ $user->email }}</dd>
                    </div>

                    <div class="rounded-lg border border-slate-200/70 bg-slate-50 p-4">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">ტელეფონი</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $user->phone ?: '—' }}</dd>
                    </div>
                </dl>
            </div>
        @else
            <form data-dirty-guard method="POST" action="{{ route('profile.user-info.update') }}" enctype="multipart/form-data"
                class="mt-6 space-y-5">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_editing" value="1">

                <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
                    @include('profile._avatar-section', [
                        'user' => $user,
                        'isEditing' => true,
                        'isVerified' => $isVerified,
                    ])
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-4">
                        <x-form.input name="name" label="სახელი" minlength="2" required value="{{ $user->name }}" />
                        <x-form.input name="surname" label="გვარი" minlength="2" required value="{{ $user->surname }}" />
                        <x-form.input name="nickname" label="ზედმეტსახელი" minlength="2" required
                            value="{{ $user->nickname }}" />
                    </div>

                    <div class="space-y-4">
                        <x-form.input name="email" type="email" label="ელ.ფოსტა" required value="{{ $user->email }}"
                            :infoMessage="$requireEmailVerification
                                ? 'ცვლილების შემდეგ საჭირო გახდება ხელახლა ვერიფიკაცია.'
                                : ''" />

                        <x-form.input name="phone" type="tel" label="ტელეფონი" placeholder="000 00 00 00"
                            pattern="^(\\+995\\s?)?(\\d{3}\\s?\\d{3}\\s?\\d{3}|\\d{3}\\s?\\d{2}\\s?\\d{2}\\s?\\d{2})$"
                            :required="$requirePhoneVerification" value="{{ $user->phone }}" :infoMessage="$requirePhoneVerification
                                ? 'ცვლილების შემდეგ საჭირო გახდება ხელახლა ვერიფიკაცია.'
                                : ''" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <x-button type="submit" data-dirty-submit>ცვლილებების შენახვა</x-button>

                    <a href="{{ route('profile.user-info') }}"
                        class="inline-flex items-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400/20">
                        გაუქმება
                    </a>
                </div>
            </form>
        @endif
    </div>
</section>
