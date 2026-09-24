@php
    use App\Configuration\Features;
    use App\Support\SessionScope;
    use Illuminate\Support\Facades\Gate;

    $activeLock = $content->getActiveLock();
    $canEdit = Gate::allows('edit', [$content, $version]);
    $pollingInterval = $canEdit ? Features::detailsPollingInterval() : null;
@endphp
@props([
    'content',
    'version',
    'explicitVersion' => false,
    'id' => 'details-action-buttons-header',
    'poll' => true,
    'oob' => false,
    'includeOob' => false,
])

<div
    id="{{ $id }}"
    class="action-buttons-container"
    @if ($oob)
        hx-swap-oob="outerHTML:#{{ $id }}"
    @endif
    @if ($poll && $pollingInterval)
        hx-get="{{ route('content.details.action-buttons', [$content, 'version' => $version->id, 'explicitVersion' => $explicitVersion ? 1 : 0]) }}"
        hx-trigger="every {{ $pollingInterval }}"
        hx-swap="outerHTML"
    @endif
>
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
        href="{{ route('content.edit', [$content, $version]) }}"
        @class([
            'btn btn-secondary d-flex gap-2 text-nowrap',
            'disabled' => $activeLock,
        ])
        @disabled($activeLock)
    >
        <x-icon name="{{ $activeLock ? 'lock' : 'pencil' }}" />
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
        data-confirm-ok-class="btn-danger"
        title="{{ trans('messages.delete') }}"
    >
        <x-icon name="trash" />
        <span class="visually-hidden">{{ trans('messages.delete') }}</span>
    </button>
@endcan

@can('admin')
    <button
        class="btn btn-outline-warning d-flex gap-2 text-nowrap"
        hx-get="{{ route('admin.content-exclusions.dialog', [$content]) }}"
        hx-target="#modal-container"
        hx-swap="beforeend"
        data-modal="true"
        title="Exclude content"
    >
        <x-icon name="slash-circle" />
        <span class="visually-hidden">Exclude</span>
    </button>
@endcan

@cannot('delete', $content)
    @if($activeLock)
        {{ trans('messages.the-lock-is-held-by-since', ['name' => $activeLock?->user->name ?? 'unknown', 'datetime' => $activeLock?->created_at ?? '?' ]) }}
    @endif
@endcannot
</div>

@if ($includeOob)
    <x-content.details.action-buttons
        :$content
        :$version
        :$explicitVersion
        id="details-action-buttons-sidebar"
        :poll="false"
        :oob="true"
        :includeOob="false"
    />
@endif
