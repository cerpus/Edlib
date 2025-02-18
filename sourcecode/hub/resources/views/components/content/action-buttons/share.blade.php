<a
    href="{{ $content->shareUrl }}"
    class="btn btn-secondary btn-sm me-1"
    target="_blank"
    hx-get="{{ $content->shareDialogUrl }}"
    hx-target="#modal-container"
    hx-swap="beforeend"
    data-modal="true"
>
    {{ trans('messages.share') }}
</a>
