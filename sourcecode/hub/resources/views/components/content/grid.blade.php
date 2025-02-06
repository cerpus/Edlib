@props(['contents', 'titlePreviews' => false])

<div class="row g-3">
    @foreach ($contents as $content)
        <div class="col">
            <x-content.card :$content :$titlePreviews />
        </div>
    @endforeach
</div>
