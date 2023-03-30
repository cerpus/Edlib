<div class="grid">
    @foreach ($contents as $content)
        <x-content-card :content="$content" />
    @endforeach
</div>
{{ $contents->links() }}
