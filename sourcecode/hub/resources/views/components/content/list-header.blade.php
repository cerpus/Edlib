@props(['layout', 'total'])

<div class="position-relative">
    <div class="row flex-row align-items-center ps-3 pe-3" aria-hidden="true">
        <div class="col fw-bold mb-1">
            {{ trans_choice('messages.num-content-found', $total) }}
            <x-spinner id="content-loading" class="ms-3" />
        </div>
        <div class="col-2 fw-bold">
            {{ trans('messages.last-changed') }}
        </div>
        <div class="col-2 fw-bold">
            {{ trans('messages.author') }}
        </div>
        <div class="col-2 fw-bold">
            {{ trans('messages.language') }}
        </div>
        <div class="col-2 fw-bold">
            {{ trans('messages.views') }}
        </div>
    </div>

    <x-content.layout-toggle :current="$layout" />
</div>
