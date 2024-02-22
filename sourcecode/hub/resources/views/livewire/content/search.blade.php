@props([
    'showDrafts' => false,
    'layout' => 'grid',
    'query' => '',
    'language' => '',
    'languageOptions' => [],
    'sortBy' => '',
    'sortOptions' => [],
    'user' => null,
    'titlePreviews' => false,
])
@php($mine = $user && $user->is(auth()->user()))
@php($hasQuery = $query !== '')
<div>
    <x-content.search
        :$query
        :$language
        :$languageOptions
        :$sortBy
        :$sortOptions
    />

    @unless ($contents->isEmpty())
        <div wire:loading.delay class="spinner-border text-info" role="status">
            <span class="visually-hidden">{{ trans('messages.loading') }}</span>
        </div>

        <div wire:loading.delay.remove>
            @if ($layout === 'grid')
                <x-content.grid-header :$layout :total="$contents->total()" />
                <x-content.grid :contents="$contents" :$showDrafts :$titlePreviews />
            @else
                <x-content.list-header :$layout :total="$contents->total()" />
                <x-content.list :contents="$contents" :$showDrafts :$titlePreviews />
            @endif

            {{ $contents->links() }}
        </div>
    @else
        <x-big-notice>
            <x-slot:title>
                @if ($hasQuery)
                    {{ trans('messages.no-results-found') }}
                @elseif ($mine)
                    {{ trans('messages.you-have-no-content-yet') }}
                @else
                    {{ trans('messages.no-content-created-yet') }}
                @endif
            </x-slot:title>

            <x-slot:description>
                @if ($hasQuery)
                    {{ trans('messages.no-results-found-description') }}
                @elseif ($mine)
                    {{ trans('messages.you-have-no-content-yet-description') }}
                @else
                    {{ trans('messages.no-content-created-yet-description') }}
                @endif
            </x-slot:description>

            @if ($mine)
                <x-slot:actions>
                    <a href="{{ route('content.index') }}" class="btn btn-secondary">
                        {{ trans('messages.explore-content') }}
                    </a>

                    <a href="{{ route('content.create') }}" class="btn btn-primary">
                        {{ trans('messages.create-content') }}
                    </a>
                </x-slot:actions>
            @endif
        </x-big-notice>
    @endunless
</div> {{-- Livewire root element --}}
