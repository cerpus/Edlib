<div class="col-md-6 col-lg-6 d-md-block d-none">
    @php
        $labels = ['Media', 'Interactive Video', 'Text', 'Cerpus Image'];
    @endphp

    @foreach ($labels as $label)
        <x-badge-component :label="$label" />
    @endforeach
</div>
