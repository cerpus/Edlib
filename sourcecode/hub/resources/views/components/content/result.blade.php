@props(['contents', 'filter', 'mine' => false])

@unless ($contents->isEmpty())
    @if ($filter->getLayout() === 'grid')
        <x-content.grid-header :layout="$filter->getLayout()" :total="$contents->total()" />
        <x-content.grid :$contents />
    @else
        <x-content.list-header :layout="$filter->getLayout()" :total="$contents->total()" />
        <x-content.list :$contents />
    @endif

    <div hx-boost="true" hx-target="#content" class="mt-3">
        {{ $contents->withQueryString()->links() }}
    </div>
@else
    @if ($filter->getLayout() === 'grid')
        <x-content.grid-header :layout="$filter->getLayout()" :total="$contents->total()" />
    @else
        <x-content.list-header :layout="$filter->getLayout()" :total="$contents->total()" />
    @endif
    <x-big-notice>
        <x-slot:title>
            @if ($filter->hasQuery())
                {{ trans('messages.no-results-found') }}
            @elseif ($mine)
                {{ trans('messages.you-have-no-content-yet') }}
            @else
                {{ trans('messages.no-content-created-yet') }}
            @endif
        </x-slot:title>

        <x-slot:description>
            @if ($filter->hasQuery())
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
