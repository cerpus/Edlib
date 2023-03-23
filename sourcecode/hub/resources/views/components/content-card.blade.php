<article class="content-card">
    <h1>
        <a href="">{{ $content->latestVersion->resource->title }}</a>
    </h1>
    <nav>
        <a href="{{ route('content.preview', [$content->id]) }}">Preview</a>
    </nav>
</article>
