<article class="card content-card">
    <div class="card-body">
        <h5 class="card-title">
            <a href="{{ route('content.preview', [$content->id]) }}">
                {{ $content->latestVersion->resource->title }}
            </a>
        </h5>

        @foreach ($content->users as $user)
            <li>{{ $user->name }} ({{ $user->pivot->role }})</li>
        @endforeach
    </div>

    <nav class="card-footer">
        <p>
            <a href="{{ route('content.preview', [$content->id]) }}">{{ trans('messages.preview') }}</a>
            <a href="{{ route('content.edit', [$content->id]) }}">{{ trans('messages.edit') }}</a>
        </p>
    </nav>
</article>
