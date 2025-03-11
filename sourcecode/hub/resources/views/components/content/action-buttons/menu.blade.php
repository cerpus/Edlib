<div class="dropup">
    <button
        type="button"
        class="btn btn-sm btn-secondary border-1 dropdown-toggle action-menu-toggle"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        aria-label="{{ trans('messages.toggle-menu') }}"
    >
        <x-icon name="three-dots-vertical" />
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @isset($shareUrl)
            <li>
                <a
                    href="{{ $shareUrl }}"
                    class="dropdown-item share-button"
                    hx-get="{{ $shareDialogUrl }}"
                    hx-target="#modal-container"
                    hx-swap="beforeend"
                    data-modal="true"
                >
                    <x-icon name="share" class="me-2" />
                    {{ trans('messages.share') }}
                </a>
            </li>
        @endisset

        @isset($detailsUrl)
            <li>
                <a href="{{ $detailsUrl }}" class="dropdown-item">
                    <x-icon name="info-lg" class="me-2" />
                    {{ trans('messages.details') }}
                </a>
            </li>
        @endisset

        @isset($copyUrl)
            <li>
                <x-form action="{{ $copyUrl }}">
                    <button class="dropdown-item">
                        <x-icon name="copy" class="me-2" />
                        {{ trans('messages.copy') }}
                    </button>
                </x-form>
            </li>
        @endisset

        @isset($deleteUrl)
            <li>
                <button
                    class="dropdown-item"
                    hx-delete="{{ $deleteUrl }}"
                    hx-confirm="{{ trans('messages.delete-content-confirm-text') }}"
                    data-confirm-title="{{ trans('messages.delete-content') }}"
                    data-confirm-ok="{{ trans('messages.delete-content') }}"
                >
                    <x-icon name="trash" class="me-2" />
                    {{ trans('messages.delete') }}
                </button>
            </li>
        @endisset
    </ul>
</div>
