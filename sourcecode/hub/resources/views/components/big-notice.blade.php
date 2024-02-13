@props(['title' => null, 'description' => null, 'actions' => null])
<div class="big-notice d-flex flex-column justify-content-center text-center">
    @if ($title)
        <h1 class="text-secondary">{{ $title }}</h1>
    @endif

    @if ($description)
        <p>{{ $description }}</p>
    @endif

    @if ($actions)
        <div class="d-flex gap-3 flex-md-row flex-wrap justify-content-center">
            {{ $actions }}
        </div>
    @endif
</div>
