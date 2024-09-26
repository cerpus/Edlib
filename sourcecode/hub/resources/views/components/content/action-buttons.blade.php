@props(['content', 'version', 'showPreview' => false, 'mine' => false])
@php
    use App\Support\SessionScope;
    use Illuminate\Support\Facades\Gate;

    $canUse = Gate::allows('use', [$content, $version]);
    $canEdit = Gate::allows('edit', $content);
    $canView = Gate::allows('view', $content);
    $canDelete = $mine && Gate::allows('delete', $content);
    $canCopy = Gate::allows('copy', $content);
@endphp
@if($canUse)
    <x-form action="{{ $version->getUseUrl() }}" method="POST">
        <button class="btn btn-primary btn-sm me-1 content-use-button">
            {{ trans('messages.use-content') }}
        </button>
    </x-form>
@endif
@if($canEdit)
    <a
        href="{{ route('content.edit', [$content, $version]) }}"
        class="btn btn-secondary btn-sm d-none d-md-inline-block me-1"
    >
        {{ trans('messages.edit-content') }}
    </a>
@endif
@if($canView || $canEdit || $canCopy || $canDelete)
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
            @if($canView)
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
                    @if ($showPreview)
                        <x-content.preview-link :$version class="dropdown-item">
                            <x-icon name="display" class="me-2" />
                            {{ trans('messages.preview') }}
                        </x-content.preview-link>
                    @else
                        <a href="{{ $content->getDetailsUrl() }}" class="dropdown-item">
                            <x-icon name="info-lg" class="me-2" />
                            {{ trans('messages.details') }}
                        </a>
                    @endif
                </li>
            @endif
            @if($canEdit)
                <li class="d-md-none">
                    <a href="{{ route('content.edit', [$content, $version]) }}" class="dropdown-item content-edit-link">
                        <x-icon name="pencil" class="me-2" />
                        {{ trans('messages.edit-content') }}
                    </a>
                </li>
            @endif
            @if($canCopy)
                <li>
                    <x-form action="{{ route('content.copy', [$content, $version]) }}">
                        <button class="dropdown-item">
                            <x-icon name="copy" class="me-2" />
                            {{ trans('messages.copy') }}
                        </button>
                    </x-form>
                </li>
            @endif
            @if($canDelete)
                <li>
                    <button
                        class="dropdown-item"
                        hx-delete="{{ route('content.delete', [$content]) }}"
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
