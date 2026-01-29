@php
	use Illuminate\Support\Facades\Storage;
@endphp

<section aria-labelledby="forum-categories" class="w-full">
	<div class="mx-auto max-w-4xl px-3 sm:px-0">
		<h2 id="forum-categories" class="sr-only">ფორუმის კატეგორიები</h2>

		<ul role="list" class="space-y-3">
			@forelse ($categories ?? [] as $category)
				@php
					$hasAd = filled($category->ad ?? null);

					$adImage = $hasAd && filled($category->ad->image ?? null)
						? Storage::url($category->ad->image)
						: null;
				@endphp

				<li>
					<button type="button"
						class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition sm:px-5 hover:shadow-md">
						<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">

							{{-- LEFT: name + ad --}}
							<div class="flex min-w-0 flex-1 flex-col gap-2 text-start sm:flex-row sm:items-center sm:gap-4">
								<p class="text-base font-semibold text-slate-900 sm:truncate">
									{{ $category->name }}
								</p>

								@if ($hasAd)
									@if ($adImage)
										<div class="relative me-auto h-10 w-36 shrink-0 sm:m-auto">
											<span
												class="absolute -right-1.5 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-md bg-blue-500 px-1 text-[10px] font-bold leading-none text-white shadow-sm">
												AD
											</span>
											<a href="{{ $category->ad->link }}" target="_blank" class="cursor-alias">
												<img src="{{ $adImage }}" alt="{{ $category->ad->name }} რეკლამა" loading="lazy"
													class="h-full w-full rounded-lg object-contain p-0.5  ring-1 ring-slate-200">
											</a>
										</div>
									@else
										<a href="{{ $category->ad->link }}" target="_blank" class="cursor-alias">
											<div
												class="me-auto inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 sm:m-auto">
												<x-app-icon name="megaphone" class="h-4 w-4 shrink-0 text-orange-500" />

												<span class="wrap-break-word whitespace-normal underline">
													{{ $category->ad->name }}
												</span>
											</div>
										</a>
									@endif
								@endif
							</div>

							{{-- RIGHT: count + arrow --}}
							<div class="flex shrink-0 items-center justify-between gap-3 sm:justify-end">
								<span class="text-sm font-medium text-slate-500">
									{{ $category->topics_count ?? 0 }} თემა
								</span>

								<a href="{{ route('categories.topics', $category) }}" <span
									class="grid h-10 w-10 place-items-center rounded-full cursor-pointer border border-slate-200 bg-slate-50 text-slate-600">
									<x-app-icon name="chevron-right" class="h-4 w-4" />
									</span>
								</a>
							</div>

						</div>
					</button>
				</li>
			@empty
				<li class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-8 text-sm text-slate-500">
					კატეგორიები ჯერ არ არის დამატებული.
				</li>
			@endforelse
		</ul>
	</div>
</section>