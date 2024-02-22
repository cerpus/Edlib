@props(['layout', 'total'])

<div class="position-relative">
    <div class="row flex-row align-items-center mb-2 ps-3 pe-3" aria-hidden="true">
        <div class="col fw-bold mb-1">
            {{ trans_choice('messages.num-content-found', $total) }}
        </div>
    </div>

    <x-content.layout-toggle :current="$layout" />
</div>

