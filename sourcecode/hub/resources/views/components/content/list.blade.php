@props(['contents', 'showDrafts' => false, 'titlePreviews' => false])

<div wire:loading.delay.remove class="row row-cols-1 g-3 mb-3">
    @foreach ($contents as $content)
        <div class="col">
            <x-content.list-item :$content :$showDrafts :$titlePreviews />
        </div>
    @endforeach
</div>
