@props(['main' => '', 'sidebar' => ''])

<div class="row col-12">
    {{ $top }}
</div>
<div class="row row-gap-3">
    <div class="col-12 col-lg-9">
        {{ $main }}
    </div>

    <aside class="col-12 col-lg-3">
        {{ $sidebar }}
    </aside>
</div>
