@extends('master')

@section('title', $category->name . ' - თემები')

@section('content')
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 px-4 sm:px-6 lg:px-0">
        <a href="{{ route('page.home') }}"
            class="inline-flex items-center gap-1 text-sm font-medium text-slate-600 transition hover:text-slate-900">
            <x-app-icon name="chevron-left" class="h-4 w-4" />
            უკან დაბრუნება
        </a>
        @include('topics.partials.category-header', ['category' => $category])

        @include('topics.partials.category-toolbar', [
            'category' => $category,
            'search' => $search,
            'scope' => $scope ?? 'all',
        ])
           @include('topics.partials.topic-list', ['topics' => $topics])
        <div class="flex justify-center">
                {{ $topics->links('components.pagination') }}
            </div>
        </div>

        <x-ui.modal id="topic-create-modal" title="თემის გახსნა" size="md">
            <form method="POST" action="{{ route('categories.topics.store', $category) }}" class="space-y-4">
                @csrf
                <x-form.input
                    name="title"
                    label="სათაური"
                    placeholder="შეიყვანეთ თემის სათაური"
                    required
                />
                <div class="flex justify-end gap-2">
                    <x-button type="button" variant="secondary" data-modal-close>გაუქმება</x-button>
                    <x-button type="submit">შექმნა</x-button>
                </div>
            </form>
        </x-ui.modal>
@endsection

@push('scripts')
    @if ($errors->has('title'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.UIModal?.open('topic-create-modal');
            });
        </script>
    @endif
@endpush
