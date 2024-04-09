@props(['layout', 'total'])

<div class="row row-col-1 ps-2 pe-2 mt-4">
    <div class="col position-relative">
        <div class="row flex-row align-items-center">
            <div class="col-4 fw-bold mb-1">
                <x-content.status
                    id="content-loading"
                    :loadingMessage="trans('messages.loading')"
                    :doneMessage="trans_choice('messages.num-content-found', $total)"
                />
            </div>
            <div class="col-2 fw-bold" aria-hidden="true">
                {{ trans('messages.last-changed') }}
            </div>
            <div class="col-2 fw-bold" aria-hidden="true">
                {{ trans('messages.author') }}
            </div>
            <div class="col-2 fw-bold" aria-hidden="true">
                {{ trans('messages.language') }}
            </div>
            <div class="col-2 fw-bold" aria-hidden="true">
                {{ trans('messages.views') }}
            </div>
        </div>

        <x-content.layout-toggle :current="$layout" />
    </div>
</div>
