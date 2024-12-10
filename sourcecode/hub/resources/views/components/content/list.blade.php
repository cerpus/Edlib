@props(['contents', 'titlePreviews' => false])

<div class="row row-cols-1 g-3">
    @foreach ($contents as $content)
        <div class="col">
            <x-content.list-item :$content :$titlePreviews />
        </div>
    @endforeach
</div>
