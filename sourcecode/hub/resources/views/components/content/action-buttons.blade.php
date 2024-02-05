@can('use', $content)
    <x-form action="{{ route('content.use', [$content]) }}" method="POST">
        <button class="btn btn-primary btn-sm me-1">
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
            class="btn btn-sm btn-secondary border-0 dropdown-toggle"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            aria-label="{{ trans('messages.toggle-menu') }}"
        >
            <x-icon name="three-dots-vertical" />
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            @can('view', $content)
                <li>
                    <a href="{{ route('content.details', [$content->id]) }}" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#previewModal">
                        <x-icon name="info-lg" class="me-2" />
                        {{ trans('messages.preview') }}
                    </a>
                </li>
            @endcan
            @can('edit', $content)
                <li class="d-md-none">
                    <a href="{{ route('content.edit', [$content->id]) }}" class="dropdown-item">
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
