@php($contentLayout = \Illuminate\Support\Facades\Session::get('contentLayout', 'grid'))
<div class="position-relative">
    <div class="row flex-row align-items-center mb-2 ps-3 pe-3">
        <div class="col fw-bold mb-1">
            {{ trans_choice('messages.num-content-found', $contents->total()) }}
        </div>
        @if ($contentLayout === 'list')
            <div class="col-2">
                {{ trans('messages.last-changed') }}
            </div>
            <div class="col-2">
                {{ trans('messages.author') }}
            </div>
            <div class="col-2">
                {{ trans('messages.language') }}
            </div>
            <div class="col-2">
                {{ trans('messages.views') }}
            </div>
        @endif
    </div>
    <div class="d-none d-sm-block position-absolute top-0 end-0">
        @if ($contentLayout === 'grid')
            <a
                href="{{ route('content.layout') }}"
                class="btn p-0 fs-4"
                title="{{ trans('messages.result-list') }}"
                aria-description="{{ trans('messages.result-list-desc') }}"
                role="button"
            >
                <x-icon name="list" />
            </a>
        @else
            <a
                href="{{ route('content.layout') }}"
                class="btn p-0 fs-4"
                title="{{ trans('messages.result-grid') }}"
                aria-description="{{ trans('messages.result-list-desc') }}"
                role="button"
            >
                <x-icon name="grid-fill" />
            </a>
        @endif
    </div>
</div>
<div wire:loading.delay class="spinner-border text-info" role="status">
    <span class="visually-hidden">{{ trans('messages.loading') }}</span>
</div>
@if ($contentLayout === 'grid')
    <div wire:loading.delay.remove class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-3 row-cols-xl-3 row-cols-xxl-4 g-3 mb-3">
        @foreach ($contents as $content)
            <div class="col">
                <x-content.card
                    :$content
                    :show-drafts="$showDrafts ?? false"
                />
            </div>
        @endforeach
    </div>
@else
    <div wire:loading.delay.remove class="row row-cols-1 g-3 mb-3">
        @foreach ($contents as $content)
            <div class="col">
                <x-content.list
                    :$content
                    :show-drafts="$showDrafts ?? false"
                />
            </div>
        @endforeach
    </div>
@endif

{{ $contents->links() }}

<x-preview-modal />
<x-delete-modal />
