@php use App\Support\SessionScope; @endphp
@props(['content', 'version', 'explicitVersion'])

@can('use', $content)
    <x-form action="{{ route('content.use', [$content]) }}">
        <button
            class="btn btn-primary d-flex gap-2 text-nowrap"
            aria-label="{{ trans('messages.use-content')}}"
            title="{{ trans('messages.use-content')}}"
        >
            <x-icon name="check-lg" />
            <span class="flex-grow-1">{{ trans('messages.use-content')}}</span>
        </button>
    </x-form>
@endcan

@can('edit', $content)
    <a
        class="btn btn-secondary d-flex gap-2 text-nowrap"
        href="{{ route('content.edit', [$content, $version]) }}"
        aria-label="{{ trans('messages.edit')}}"
        title="{{ trans('messages.edit')}}"
        role="button"
    >
        <x-icon name="pencil" />
        <span class="flex-grow-1">{{ trans('messages.edit')}}</span>
    </a>
@endcan

@if (!$explicitVersion && $version->published)
    <a
        class="btn btn-secondary d-flex gap-2 text-nowrap"
        href="{{ route('content.share', [$content, SessionScope::TOKEN_PARAM => null]) }}"
        target="_blank"
        role="button"
        data-share-success-message="{{ trans('messages.share-copied-url-success') }}"
        data-share-failure-message="{{ trans('messages.share-copied-url-failed') }}"
        aria-label="{{ trans('messages.share') }}"
        title="{{ trans('messages.share') }}"
    >
        <x-icon name="share" />
        <span class="flex-grow-1">{{ trans('messages.share') }}</span>
    </a>
@endif

@can('delete', $content)
    <button
        class="btn btn-outline-danger gap-2 d-flex text-nowrap delete-content-button"
        hx-delete="{{ route('content.delete', [$content]) }}"
        hx-confirm="{{ trans('messages.confirm-delete-content') }}"
        aria-label="{{ trans('messages.delete') }}"
        title="{{ trans('messages.delete') }}"
    >
        <x-icon name="trash" />
    </button>
@endcan
