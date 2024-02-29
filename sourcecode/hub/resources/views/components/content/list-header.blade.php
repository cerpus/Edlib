@props(['layout', 'total'])

<div class="position-relative">
    <div class="row flex-row align-items-center mb-2 ps-3 pe-3" aria-hidden="true">
        <div class="col fw-bold mb-1">
            {{ trans_choice('messages.num-content-found', $total) }}
        </div>
        <div class="col-2">
            {{ trans('messages.last-changed') }}
        </div>
        <div class="col-2">
            {{ trans('messages.author') }}
        </div>
        <div class="col-2">
            {{ trans('messages.language') }}
        </div>
        <div class="col-2">
            {{ trans('messages.views') }}
        </div>
    </div>

    <x-content.layout-toggle :current="$layout" />
</div>
