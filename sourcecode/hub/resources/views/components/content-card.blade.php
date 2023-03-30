<article class="content-card">
    <h1>
        <a href="">{{ $content->latestVersion->resource->title }}</a>
    </h1>

    @foreach ($content->users as $user)
        <li>{{ $user->name }} ({{ $user->pivot->role }})</li>
    @endforeach

    <nav>
        <p>
            <a href="{{ route('content.preview', [$content->id]) }}">{{ trans('messages.preview') }}</a>
            <a href="{{ route('content.edit', [$content->id]) }}">{{ trans('messages.edit') }}</a>
        </p>
    </nav>
</article>
