<div class="row row-cols-4 gx-2 gy-2">
    @foreach ($contents as $content)
        <div class="col"><x-content-card :content="$content" /></div>
    @endforeach
</div>
{{ $contents->links() }}
