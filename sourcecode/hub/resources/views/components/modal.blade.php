<div {{ $attributes->except([
    'sm',
    'md',
    'lg',
    'xl',
    'xxl',
    'fullscreen-sm-down',
    'fullscreen-md-down',
    'fullscreen-lg-down',
    'fullscreen-xl-down',
    'fullscreen-xxl-down',
])->class('modal') }}>
    <div @class([
        'modal-dialog',
        'modal-sm' => $sm ?? false,
        'modal-md' => $md ?? false,
        'modal-lg' => $lg ?? false,
        'modal-xl' => $xl ?? false,
        'modal-xxl' => $xxl ?? false,
        'modal-fullscreen-sm-down' => $fullscreenSm_down ?? false,
        'modal-fullscreen-md-down' => $fullscreenMdDown ?? false,
        'modal-fullscreen-lg-down' => $fullscreenLgDown ?? false,
        'modal-fullscreen-xl-down' => $fullscreenXlDown ?? false,
        'modal-fullscreen-xxl-down' => $fullscreenXxlDown ?? false,
    ])>
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ $title }}</h4>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="{{ trans('messages.close') }}"
                ></button>
            </div>

            <div class="modal-body">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
