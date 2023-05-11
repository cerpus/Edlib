<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 mb-3">
    @foreach ($contents as $content)
        <div class="col">
            <x-content-card
                :content="$content"
                :show-drafts="$showDrafts ?? false"
            />
        </div>
    @endforeach
</div>

{{ $contents->links() }}
