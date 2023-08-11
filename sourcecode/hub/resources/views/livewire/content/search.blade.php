<div>
    <x-content.search :query="$query"/>

    @if ($results->isEmpty())
        <p>{{ trans('messages.alert-no-results-found') }}</p>
    @else
        <x-content.grid :contents="$results"/>
    @endif
</div>
