@props(['content', 'showPreview' => false])

@if($content->useUrl)
    <x-form action="{{ $content->useUrl }}" method="POST">
        <button class="btn btn-primary btn-sm me-1 content-use-button">
            {{ trans('messages.use-content') }}
        </button>
    </x-form>
@endif
@if($content->editUrl)
    <a
        href="{{ $content->editUrl }}"
        class="btn btn-secondary btn-sm d-none d-md-inline-block me-1"
    >
        {{ trans('messages.edit-content') }}
    </a>
@endif
@if($content->shareUrl || $content->editUrl || $content->copyUrl || $content->deleteUrl)
    <div class="dropup">
        <button
            type="button"
            class="btn btn-sm btn-secondary border-0 dropdown-toggle action-menu-toggle"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            aria-label="{{ trans('messages.toggle-menu') }}"
        >
            <x-icon name="three-dots-vertical" />
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            @if($content->shareUrl)
                <li>
                    <a
                        href="{{ $content->shareUrl }}"
                        class="dropdown-item share-button"
                        data-share-success-message="{{ trans('messages.share-copied-url-success') }}"
                        data-share-failure-message="{{ trans('messages.share-copied-url-failed') }}"
                        role="button"
                        target="_blank"
                    >
                        <x-icon name="share" class="me-2" />
                        {{ trans('messages.share') }}
                    </a>
                </li>
                <li>
                    @if ($showPreview)
                        <x-content.preview-link :detailsUrl="$content->detailsUrl" :previewUrl="$content->previewUrl" class="dropdown-item">
                            <x-icon name="display" class="me-2" />
                            {{ trans('messages.preview') }}
                        </x-content.preview-link>
                    @else
                        <a href="{{ $content->detailsUrl }}" class="dropdown-item">
                            <x-icon name="info-lg" class="me-2" />
                            {{ trans('messages.details') }}
                        </a>
                    @endif
                </li>
            @endif
            @if($content->editUrl)
                <li class="d-md-none">
                    <a href="{{ $content->editUrl }}" class="dropdown-item content-edit-link">
                        <x-icon name="pencil" class="me-2" />
                        {{ trans('messages.edit-content') }}
                    </a>
                </li>
            @endif
            @if($content->copyUrl)
                <li>
                    <x-form action="{{ $content->copyUrl }}">
                        <button class="dropdown-item">
                            <x-icon name="copy" class="me-2" />
                            {{ trans('messages.copy') }}
                        </button>
                    </x-form>
                </li>
            @endif
            @if($content->deleteUrl)
                <li>
                    <button
                        class="dropdown-item"
                        hx-delete="{{ $content->deleteUrl }}"
                        hx-confirm="{{ trans('messages.delete-content-confirm-text') }}"
                        data-confirm-title="{{ trans('messages.delete-content') }}"
                        data-confirm-ok="{{ trans('messages.delete-content') }}"
                    >
                        <x-icon name="trash" class="me-2" />
                        {{ trans('messages.delete') }}
                    </button>
                </li>
            @endif
        </ul>
    </div>
@endif
