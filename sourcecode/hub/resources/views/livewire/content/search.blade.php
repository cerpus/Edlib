<div>
    <x-content.search :query="$query"/>

    @unless ($results->isEmpty())
        <x-content.grid :contents="$results"/>
    @else
        <div class="no-content-found d-flex flex-column justify-content-center align-items-center">
            <h1 class="text-secondary d-flex">{{ trans('messages.alert-no-search-content-found-header') }}</h1>
            <p class="d-flex">{{ trans('messages.alert-no-search-content-found-description') }}</p>

            <div class="d-flex gap-3 flex-column flex-md-row">
                <a href="{{ route('content.index') }}" class="btn btn-primary" > {{ trans('messages.find-content') }} </a>
                <a href="{{ route('content.create') }}" class="btn btn-secondary"> {{ trans('messages.create-content') }} </a>
            </div>
        </div>
    @endunless
</div>
