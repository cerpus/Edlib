@props(['title' => null, 'description' => null, 'actions' => null])
<div class="d-flex flex-column justify-content-center text-center flex-max big-notice">
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
