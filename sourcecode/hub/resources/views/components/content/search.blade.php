@props(['contents', 'filter', 'mine' => false, 'showDrafts' => false])

<div class="row my-3" id="content">
    <search class="col-3 border rounded d-md-block d-none" aria-description="{{trans('messages.filter-section')}}">
        <x-filter.side :$filter />
    </search>
    <section class="col-12 col-md-9">
        <search class="mb-3">
            <x-filter.top :$contents :$filter />
        </search>
        @unless ($contents->isEmpty())
            @if ($filter->getLayout() === 'grid')
                <x-content.grid-header :layout="$filter->getLayout()" :total="$contents->total()" />
                <x-content.grid :$contents :$showDrafts :titlePreviews="$filter->isTitlePreview()" :$mine />
            @else
                <x-content.list-header :layout="$filter->getLayout()" :total="$contents->total()" />
                <x-content.list :$contents :$showDrafts :titlePreviews="$filter->isTitlePreview()" :$mine />
            @endif

        <div hx-boost="true" hx-target="#content">
            {{ $contents->withQueryString()->links() }}
        </div>
        @else
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
    </section>
</div>
