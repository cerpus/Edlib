@props(['mine' => false, 'hasQuery' => $query !== ''])
<div>
    <x-content.search
        :$query
        :$filterLang
        :$languageOptions
        :$sortBy
        :$sortOptions
    />

    @unless ($results->isEmpty())
        <x-content.grid :contents="$results"/>
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
