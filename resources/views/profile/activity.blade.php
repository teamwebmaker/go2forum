@extends('layouts.user-profile')

@section('title', 'აქტივობები')

@section('profile-content')
	<section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-black/5 space-y-6">
		<header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
			<div class="space-y-1">
				<h2 class="text-lg font-semibold text-slate-900">აქტივობის ისტორია</h2>
				<p class="text-sm text-slate-600">აქ ნახავ შენს შექმნილ თემებს და საუბრის სივრცეებს, სადაც წევრი ხარ.</p>
			</div>

			<div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-700">
				<span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5">
					საუბრები: {{ $conversations->total() }}
				</span>
				<span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5">
					თემები: {{ $topics->total() }}
				</span>
			</div>
		</header>

		<div class="grid gap-6 xl:grid-cols-2">
			{{-- Conversations --}}
			<article id="activity-conversations" class="rounded-xl border border-slate-200/80 bg-slate-50/40">
				<header class="flex items-center justify-between border-b border-slate-200/80 px-4 py-3">
					<h3 class="text-sm font-semibold text-slate-900">ჩემი საუბრები</h3>
					<span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
						{{ $conversations->total() }}
					</span>
				</header>

				@if ($conversations->isEmpty())
					<div class="px-4 py-8 text-center">
						<p class="text-sm font-medium text-slate-700">საუბრები ჯერ არ გაქვს.</p>
						<p class="mt-1 text-xs text-slate-500">როცა თემაში ან პირადში ჩაერთვები, ისტორია აქ გამოჩნდება.</p>
					</div>
				@else
					<ul class="divide-y divide-slate-200/80">
						@foreach ($conversations as $conversation)
							@php
								$membership = $conversation->participants->first();
								$isTopicConversation = $conversation->isTopic();
								$isPrivateConversation = $conversation->isPrivate();

								$partner = null;
								if ($isPrivateConversation) {
									if ((int) $conversation->direct_user1_id === (int) $user->id) {
										$partner = $conversation->directUser2;
									} elseif ((int) $conversation->direct_user2_id === (int) $user->id) {
										$partner = $conversation->directUser1;
									}
								}

								$title = $isTopicConversation
									? ($conversation->topic?->title ?? 'თემა აღარ არსებობს')
									: ($partner?->full_name ?? 'პირადი მიმოწერა');

								$partnerBadgeColor = $isPrivateConversation
									? \App\Support\BadgeColors::forUser($partner)
									: null;

								$conversationUrl = $isTopicConversation
									? ($conversation->topic?->slug
										? route('topics.show', $conversation->topic->slug)
										: route('profile.activity'))
									: route('profile.messages', ['conversation' => $conversation->id]);
							 @endphp

							<li class="px-4 py-3">
								<div class="flex items-start justify-between gap-3">
									<div class="min-w-0 space-y-2">
										<div class="flex items-center gap-2">
											@if ($isPrivateConversation && $partner)
												<x-ui.avatar :user="$partner" size="xs" class="shrink-0" :showBadges="false" />
											@endif
											<div class="flex min-w-0 items-center gap-1">
												@if ($isPrivateConversation && $partnerBadgeColor)
													<x-ui.avatar-badge iconClass="{{ $partnerBadgeColor }}" iconSizeClass="size-4!"
														wrapperClass="inline-flex shrink-0" badgeClass="inline-flex" />
												@endif
												<p class="truncate text-sm font-semibold text-slate-900">{{ $title }}</p>
											</div>
										</div>

										<div class="flex flex-wrap items-center gap-1.5 text-[11px] text-slate-600">
											<span
												class="rounded-full px-2 py-0.5 font-semibold {{ $isTopicConversation ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
												{{ $isTopicConversation ? 'თემა' : 'პირადი' }}
											</span>

											<span class="rounded-full border border-slate-200 bg-white px-2 py-0.5">
												წევრები: {{ $conversation->participants_count }}
											</span>

											@if ($conversation->last_message_at)
												<span class="rounded-full border border-slate-200 bg-white px-2 py-0.5">
													ბოლო აქტივობა:
													{{ $conversation->last_message_at->locale('ka')->translatedFormat('d M Y') }}
												</span>
											@endif

											@if ($membership?->joined_at)
												<span class="rounded-full border border-slate-200 bg-white px-2 py-0.5">
													შეუერთდი:
													{{ $membership->joined_at->locale('ka')->translatedFormat('d M Y') }}
												</span>
											@endif
										</div>
									</div>

									<a href="{{ $conversationUrl }}"
										class="shrink-0 inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
										გახსნა
									</a>
								</div>
							</li>
						@endforeach
					</ul>

					@if ($conversations->hasPages())
						<div class="border-t border-slate-200/80 bg-white/70 px-4 py-3">
							{{ $conversations->onEachSide(1)->fragment('activity-conversations')->links('components.pagination') }}
						</div>
					@endif
				@endif
			</article>

			{{-- Topics --}}
			<article id="activity-topics" class="rounded-xl border border-slate-200/80 bg-slate-50/40">
				<header class="flex items-center justify-between border-b border-slate-200/80 px-4 py-3">
					<h3 class="text-sm font-semibold text-slate-900">ჩემი თემები</h3>
					<span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
						{{ $topics->total() }}
					</span>
				</header>

				@if ($topics->isEmpty())
					<div class="px-4 py-8 text-center">
						<p class="text-sm font-medium text-slate-700">თემები ჯერ არ შეგიქმნია.</p>
						<p class="mt-1 text-xs text-slate-500">დაიწყე დისკუსია კატეგორიიდან და ის აქ გამოჩნდება.</p>
						<a href="{{ route('page.home') }}"
							class="mt-4 inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
							კატეგორიებზე გადასვლა
						</a>
					</div>
				@else
					<ul class="divide-y divide-slate-200/80">
						@foreach ($topics as $topic)
							@php
								$statusClass = match ($topic->status) {
									'active' => 'bg-emerald-100 text-emerald-700',
									'closed' => 'bg-slate-200 text-slate-700',
									'disabled' => 'bg-rose-100 text-rose-700',
									default => 'bg-slate-200 text-slate-700',
								};
							 @endphp

							<li class="px-4 py-3">
								<div class="flex items-start justify-between gap-3">
									<div class="min-w-0 space-y-2">
										<p class="truncate text-sm font-semibold text-slate-900">{{ $topic->title }}</p>
										<div class="flex flex-wrap items-center gap-1.5 text-[11px] text-slate-600">
											@if ($topic->category)
												<span class="rounded-full border border-slate-200 bg-white px-2 py-0.5">
													{{ $topic->category->name }}
												</span>
											@endif

											<span class="rounded-full px-2 py-0.5 font-semibold {{ $statusClass }}">
												{{ ucfirst($topic->status) }}
											</span>

											<span class="rounded-full border border-slate-200 bg-white px-2 py-0.5">
												{{ $topic->messages_count }} კომენტარი
											</span>

											<span class="rounded-full border border-slate-200 bg-white px-2 py-0.5">
												{{ $topic->created_at->locale('ka')->translatedFormat('d M Y') }}
											</span>
										</div>
									</div>

									<a href="{{ route('topics.show', $topic->slug) }}"
										class="shrink-0 inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
										გახსნა
									</a>
								</div>
							</li>
						@endforeach
					</ul>

					@if ($topics->hasPages())
						<div class="border-t border-slate-200/80 bg-white/70 px-4 py-3">
							{{ $topics->onEachSide(1)->fragment('activity-topics')->links('components.pagination') }}
						</div>
					@endif
				@endif
			</article>
		</div>
	</section>
@endsection
