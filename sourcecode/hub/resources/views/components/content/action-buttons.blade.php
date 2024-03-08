@php use App\Support\SessionScope; @endphp
@props(['content', 'version', 'showPreview' => false])
@can('use', $content)
    <x-form action="{{ route('content.use', [$content]) }}" method="POST">
        <button class="btn btn-primary btn-sm me-1 content-use-button">
            {{ trans('messages.use-content') }}
        </button>
    </x-form>
@endcan
@can('edit', $content)
    <a
        href="{{ route('content.edit', [$content, $version]) }}"
        class="btn btn-secondary btn-sm d-none d-md-inline-block me-1"
    >
        {{ trans('messages.edit-content') }}
    </a>
@endcan
@canany(['view', 'edit'], $content)
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
            @can('view', $content)
                <li>
                    <a
                        href="{{ route('content.share', [$content, SessionScope::TOKEN_PARAM => null]) }}"
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
                    <a
                        href="{{ route('content.details', [$content]) }}"
                        class="dropdown-item"
                        @if ($showPreview)
                            hx-get="{{ route('content.preview', [$content, $version]) }}"
                            hx-target="#previewModal"
                            data-bs-toggle="modal"
                            data-bs-target="#previewModal"
                        @endif
                    >
                        @if ($showPreview)
                            <x-icon name="display" class="me-2" />
                            {{ trans('messages.preview') }}
                        @else
                            <x-icon name="info-lg" class="me-2" />
                            {{ trans('messages.details') }}
                        @endif
                    </a>
                </li>
            @endcan
            @can('edit', $content)
                <li class="d-md-none">
                    <a href="{{ route('content.edit', [$content, $version]) }}" class="dropdown-item content-edit-link">
                        <x-icon name="pencil" class="me-2" />
                        {{ trans('messages.edit-content') }}
                    </a>
                </li>
            @endcan
        </ul>
    </div>
@endcan
