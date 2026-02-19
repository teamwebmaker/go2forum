<div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-4">
	{{-- Header Content --}}
	<div>
		<h1 class="text-2xl font-semibold text-slate-900">{{ $topic->title }}</h1>
		@if ($topic->category)
			<a href="{{ route('categories.topics', $topic->category) }}"
				class="mt-2 inline-flex max-w-max items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
				{{ $topic->category->name }}
			</a>
		@endif
	</div>

	{{-- Header Actions --}}
	<div class="flex flex-col xs:flex-row gap-2">
		{{-- Refresh --}}
		<div class="justify-self-start">
			<x-button type="button" variant="secondary" size="sm" wire:click="refresh" aria-label="Refresh"
				wire:loading.attr="disabled" wire:target="refresh"
				class="rounded-lg! p-1! text-slate-600 hover:text-slate-900">
				<x-slot:icon>
					<x-app-icon name="arrow-path" class="size-3" />
				</x-slot:icon>
			</x-button>
			@auth
				{{-- Subscribe Bell --}}
				<x-ui.tooltip position="bottom" text="ამ ღილაკით გამოიწერთ თემას და მიიღებთ შეტყობინებებს.">
					<x-button type="button" variant="secondary" size="sm" wire:click="toggleSubscription"
						wire:loading.attr="disabled" wire:target="toggleSubscription"
						class="rounded-lg! p-1! text-slate-600 hover:text-slate-900 {{ $isSubscribed ? 'text-emerald-600' : 'text-slate-600' }}">
						@if ($isSubscribed)
							<x-app-icon name="bell-alert" class="size-5" />
						@else
							<x-app-icon name="bell-slash" class="size-5" />
						@endif
					</x-button>
				</x-ui.tooltip>
			@endauth
		</div>

		{{-- Add message --}}
		@if ($canPost)
			<x-button type="button" variant="primary" size="sm" wire:click="openComposer"
				class="rounded-full bg-primary-500 hover:bg-primary-600">
				კომენტარის დაწერა
			</x-button>
		@endif
	</div>
</div>