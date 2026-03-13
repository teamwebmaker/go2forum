@php
	use Illuminate\Support\Facades\Storage;
@endphp

<section aria-labelledby="forum-categories" class="w-full">
	<div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm sm:p-5">
		<ul role="list" class="space-y-3">
			@forelse ($categories ?? [] as $category)
				@php
					$ad = data_get($category, 'ad');
					$adLink = data_get($ad, 'link');
					$adName = data_get($ad, 'name');
					$adImagePath = data_get($ad, 'image');
					$hasAd = filled($ad) && filled($adLink);
					$adImage = $hasAd && filled($adImagePath)
						? Storage::url($adImagePath)
						: null;
					$topicsCount = (int) data_get($category, 'topics_count', 0);
					$messagesCount = (int) data_get($category, 'total_messages_count', 0);
				@endphp

					<li>
						<div class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(220px,0.55fr)] lg:items-center">
							<div class="rounded-2xl border border-slate-200 bg-white px-3 py-1.5 transition sm:px-5 sm:py-3">
								<div
									class="grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,1fr)_auto_auto_40px] sm:items-center sm:gap-3">
									<div class="min-w-0">
										<a href="{{ route('categories.topics', $category) }}"
											class="inline text-lg font-semibold leading-snug text-slate-900 break-words hover:text-slate-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500/40">
											{{ $category->name }}
										</a>
									</div>

									<div class="flex items-center sm:justify-center">
										<button type="button" class="group relative inline-flex items-center focus:outline-none">
											<span
												class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-sm font-semibold text-slate-700">
												<x-app-icon name="chat-bubble-left-right" class="h-3.5 w-3.5 text-slate-500" />
												<span>{{ number_format($topicsCount) }}</span>
											</span>
											<span
												class="pointer-events-none invisible absolute left-1/2 top-full z-20 mt-1 -translate-x-1/2 rounded-md border border-slate-200 bg-white px-2 py-1 text-xs font-medium text-slate-700 opacity-0 shadow-md transition group-hover:visible group-hover:opacity-100 group-focus:visible group-focus:opacity-100 group-active:visible group-active:opacity-100">
												თემები
											</span>
										</button>
									</div>

									<div class="flex items-center sm:justify-center">
										<button type="button" class="group relative inline-flex items-center focus:outline-none">
											<span
												class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-sm font-semibold text-slate-700">
												<x-app-icon name="chat-bubble-left-ellipsis" class="h-3.5 w-3.5 text-slate-500" />
												<span>{{ number_format($messagesCount) }}</span>
											</span>
											<span
												class="pointer-events-none invisible absolute left-1/2 top-full z-20 mt-1 -translate-x-1/2 rounded-md border border-slate-200 bg-white px-2 py-1 text-xs font-medium text-slate-700 opacity-0 shadow-md transition group-hover:visible group-hover:opacity-100 group-focus:visible group-focus:opacity-100 group-active:visible group-active:opacity-100">
												კომენტარები
											</span>
										</button>
									</div>

									<div class="flex items-center justify-end">
										<a href="{{ route('categories.topics', $category) }}"
											class="grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500/40">
											<x-app-icon name="chevron-right" class="h-4 w-4" />
										</a>
									</div>
								</div>
							</div>

						<div class="hidden h-full flex-col justify-center rounded-2xl bg-slate-50  lg:flex lg:bg-white">

							@if ($hasAd)
								@if ($adImage)
									<div class="relative h-17 p-1 w-full">
										<span
											class="absolute -right-1.5 -top-1.5 z-10 inline-flex h-4 min-w-4 items-center justify-center rounded-md bg-blue-500 px-1 text-[10px] font-bold leading-none text-white shadow-sm">
											AD
										</span>
										<a href="{{ $adLink }}" target="_blank" rel="noopener noreferrer" class="block h-full w-full">
											<img src="{{ $adImage }}" alt="{{ $adName }} რეკლამა" loading="lazy"
												class="h-full w-full rounded-xl bg-white object-contain p-1 ring-1 ring-slate-200">
										</a>
									</div>
								@else
									<a href="{{ $adLink }}" target="_blank" rel="noopener noreferrer"
										class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
										<x-app-icon name="megaphone" class="h-4 w-4 shrink-0 text-orange-500" />
											<span class="line-clamp-2 break-words underline">
												{{ $adName }}
											</span>
										</a>
								@endif
							@else
								<div
									class="flex min-h-16 w-full flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white px-3 text-center">
									<x-app-icon name="megaphone" class="h-4 w-4 text-slate-400" />
									<p class="mt-2 text-xs font-semibold text-slate-600">რეკლამის ადგილი</p>
								</div>
							@endif
						</div>
					</div>
				</li>
			@empty
				<li class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-8 text-sm text-slate-500">
					კატეგორიები ჯერ არ არის დამატებული.
				</li>
			@endforelse
		</ul>

		<div class="mt-4 lg:hidden">
			<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
				@forelse ($categories ?? [] as $category)
					@php
						$ad = data_get($category, 'ad');
						$adLink = data_get($ad, 'link');
						$adName = data_get($ad, 'name');
						$adImagePath = data_get($ad, 'image');
						$hasAd = filled($ad) && filled($adLink);
						$adImage = $hasAd && filled($adImagePath)
							? Storage::url($adImagePath)
							: null;
					@endphp

					<div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5">
						@if ($hasAd)
							@if ($adImage)
								<div class="relative h-20 w-full">
									<span
										class="absolute -right-1.5 -top-1.5 z-10 inline-flex h-4 min-w-4 items-center justify-center rounded-md bg-blue-500 px-1 text-[10px] font-bold leading-none text-white shadow-sm">
										AD
									</span>
									<a href="{{ $adLink }}" target="_blank" rel="noopener noreferrer" class="block h-full w-full">
										<img src="{{ $adImage }}" alt="{{ $adName }} რეკლამა" loading="lazy"
											class="h-full w-full rounded-xl bg-white object-contain p-1 ring-1 ring-slate-200">
									</a>
								</div>
							@else
								<a href="{{ $adLink }}" target="_blank" rel="noopener noreferrer"
									class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
									<x-app-icon name="megaphone" class="h-4 w-4 shrink-0 text-orange-500" />
									<span class="line-clamp-2 wrap-break-words underline">
										{{ $adName }}
									</span>
								</a>
							@endif
						@endif
						@unless ($hasAd)
							<div
								class="flex min-h-20 w-full flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white px-3 text-center">
								<x-app-icon name="megaphone" class="h-4 w-4 text-slate-400" />
								<p class="mt-2 text-xs font-semibold text-slate-600">რეკლამის ადგილი</p>
							</div>
						@endunless
					</div>
				@empty
				@endforelse
			</div>
		</div>
	</div>
</section>
