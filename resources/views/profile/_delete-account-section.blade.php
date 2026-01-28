<section class="rounded-2xl border border-rose-400/80 bg-white p-5 shadow-sm ring-1 ring-black/5">
    <header class="flex items-start flex-col sm:flex-row justify-between gap-4">
        <div class="space-y-1">
            <h2 class="text-base font-semibold text-rose-700">ანგარიშის წაშლა</h2>
            <p class="text-sm text-rose-600">
                ანგარიშისა და მასთან დაკავშირებული ყველა მონაცემის წაშლა.
            </p>
        </div>

        <x-button type="button" variant="secondary" data-modal-open="delete-account-modal"
            class="text-rose-500 border border-rose-300 hover:bg-rose-50">
            ანგარიშის წაშლა
        </x-button>
    </header>
</section>

<x-ui.modal id="delete-account-modal" title="დადასტურება" size="md">
    <p class="text-sm text-slate-700">
        დარწმუნებული ხართ, რომ გსურთ ანგარიშის საბოლოოდ წაშლა?
        ამ ქმედების შემდეგ ყველა თქვენი ინფორმაცია სამუდამოდ წაიშლება და აღდგენა ვერ მოხერხდება.
    </p>

    <x-slot:footer>
        <div class="flex justify-end gap-3">
            <x-button type="button" variant="secondary" data-modal-close>
                გაუქმება
            </x-button>
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="primary" class="bg-rose-600 border border-rose-600 hover:bg-rose-700">
                    ანგარიშის წაშლა
                </x-button>
            </form>
        </div>
    </x-slot:footer>
</x-ui.modal>