@props([
    'id' => 'htmxConfirmModal',
    'title' => '',
    'ok' => 'OK',
    'cancel' => trans('messages.cancel'),
])
<div id="{{$id}}" class="modal modal-blur fade" style="display: none" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-lg-down modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="{{$id}}-Title">
                    {{ $title }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body" id="{{$id}}-Body"></div>
            <div class="modal-footer border-0">
                <button id="{{$id}}-Ok" type="button" class="btn btn-primary">
                    {{ $ok }}
                </button>
                <button id="{{$id}}-Cancel" type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ $cancel }}
                </button>
            </div>
        </div>
    </div>
</div>
