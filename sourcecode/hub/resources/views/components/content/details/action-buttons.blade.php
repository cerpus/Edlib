@php use App\Support\SessionScope; @endphp
@props(['content', 'version', 'explicitVersion'])

@can('use', [$content, $version])
    <x-form action="{{ $version->getUseUrl() }}">
        <button class="btn btn-primary d-flex gap-2 text-nowrap">
            <x-icon name="check-lg" />
            <span class="flex-grow-1">{{ trans('messages.use-content')}}</span>
        </button>
    </x-form>
@endcan

@can('edit', $content)
    <a
        class="btn btn-secondary d-flex gap-2 text-nowrap"
        href="{{ route('content.edit', [$content, $version]) }}"
    >
        <x-icon name="pencil" />
        <span class="flex-grow-1">{{ trans('messages.edit')}}</span>
    </a>
@endcan

@can('copy', $content)
    <x-form action="{{ route('content.copy', [$content]) }}">
        <button
            class="btn btn-secondary d-flex gap-2 text-nowrap"
            type="submit"
        >
            <x-icon name="copy" />
            <span class="flex-grow-1">{{ trans('messages.copy') }}</span>
        </button>
    </x-form>
@endcan

@if (!$explicitVersion && $version->published)
    <a
        class="btn btn-secondary d-flex gap-2 text-nowrap share-button"
        href="{{ route('content.share', [$content, SessionScope::TOKEN_PARAM => null]) }}"
        target="_blank"
        hx-get="{{ route('content.share-dialog', [$content]) }}"
        hx-target="#modal-container"
        hx-swap="beforeend"
        data-modal="true"
    >
        <x-icon name="share" />
        <span class="flex-grow-1">{{ trans('messages.share') }}</span>
    </a>
@endif

@can('delete', $content)
    <button
        class="btn btn-outline-danger gap-2 d-flex text-nowrap delete-content-button"
        hx-delete="{{ route('content.delete', [$content]) }}"
        hx-confirm="{{ trans('messages.delete-content-confirm-text') }}"
        data-confirm-title="{{ trans('messages.delete-content') }}"
        data-confirm-ok="{{ trans('messages.delete-content') }}"
        title="{{ trans('messages.delete') }}"
    >
        <x-icon name="trash" />
        <span class="visually-hidden">{{ trans('messages.delete') }}</span>
    </button>
@endcan
