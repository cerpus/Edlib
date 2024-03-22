@props(['layout', 'total'])

<div class="row row-col-1 ps-2 pe-2 mt-4">
    <div class="col position-relative">
        <div class="row row-col-1 flex-row align-items-center" aria-hidden="true">
            <div class="col fw-bold mb-1">
                {{ trans_choice('messages.num-content-found', $total) }}
                <x-spinner id="content-loading" class="ms-3" />
            </div>
        </div>

        <x-content.layout-toggle :current="$layout" />
    </div>
</div>
