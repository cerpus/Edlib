<article class="content-card">
    <h1>
        <a href="">{{ $content->latestVersion->resource->title }}</a>
    </h1>
    <nav>
        <a href="{{ route('content.preview', [$content->id]) }}">{{ trans('messages.preview') }}</a>
        <a href="{{ route('content.edit', [$content->id]) }}">{{ trans('messages.edit') }}</a>
    </nav>
</article>
