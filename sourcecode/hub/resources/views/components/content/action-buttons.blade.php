@php use App\Support\SessionScope; @endphp
@can('use', $content)
    <x-form action="{{ route('content.use', [$content]) }}" method="POST">
        <button class="btn btn-primary btn-sm me-1 content-use-button">
            {{ trans('messages.use-content') }}
        </button>
    </x-form>
@endcan
@can('edit', $content)
    <a
        href="{{ route('content.edit', [$content]) }}"
        class="btn btn-secondary btn-sm d-none d-md-inline-block me-1"
    >
        {{ trans('messages.edit-content') }}
    </a>
@endcan
@canany(['view', 'edit', 'delete'], $content)
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
                        href="#"
                        class="dropdown-item"
                        data-bs-toggle="modal"
                        data-bs-target="#previewModal"
                        data-content-preview-url="{{ route('content.preview', [$content, $version]) }}"
                        data-content-share-url="{{ route('content.share', [$content, SessionScope::TOKEN_PARAM => null]) }}"
                        data-content-title="{{$version->title}}"
                        data-content-created="{{$content->created_at->isoFormat('LLLL')}}"
                        data-content-updated="{{$content->updated_at->isoFormat('LLLL')}}"
                        @can('use', $content) data-content-use-url="{{ route('content.use', [$content]) }}" @endif
                        @can('edit', $content) data-content-edit-url="{{ route('content.edit', [$content]) }}" @endif
                    >
                        <x-icon name="display" class="me-2" />
                        {{ trans('messages.preview') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('content.details', [$content->id]) }}" class="dropdown-item">
                        <x-icon name="info-lg" class="me-2" />
                        {{ trans('messages.details') }}
                    </a>
                </li>
            @endcan
            @can('edit', $content)
                <li class="d-md-none">
                    <a href="{{ route('content.edit', [$content->id]) }}" class="dropdown-item content-edit-link">
                        <x-icon name="pencil" class="me-2" />
                        {{ trans('messages.edit-content') }}
                    </a>
                </li>
            @endcan
            @can('delete', $content)
                <li>
                    <a href="#" class="btn btn-primary dropdown-item"  data-bs-toggle="modal" data-bs-target="#deletionModal">
                        <x-icon name="x-lg" class="me-2 text-danger" />
                        {{ trans('messages.delete-content') }}
                    </a>
                </li>
            @endcan
        </ul>
    </div>
@endcan
