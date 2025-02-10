@props(['contents'])

<div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-2 row-cols-xl-3 row-cols-xxl-4 g-3">
    @foreach ($contents as $content)
        <div class="col">
            <x-content.card :$content />
        </div>
    @endforeach
</div>
