@php($view = 'list')
<div class="position-relative">
    <div class="row flex-row align-items-center mb-2 ps-3 pe-3">
        <div class="col fw-bold mb-1">
            {{ trans('messages.num-content-found', ['num' => $contents->total()]) }}
        </div>
        @if ($view === 'list')
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
        @if ($view === 'list')
            <button
                type="button"
                class="btn p-0 fs-4"
                title="{{ trans('messages.result-grid') }}"
                aria-description="{{ trans('messages.result-list-desc') }}"
            >
                <x-icon name="grid-fill" />
            </button>
        @else
            <button
                type="button"
                class="btn p-0 fs-4"
                title="{{ trans('messages.result-list') }}"
                aria-description="{{ trans('messages.result-list-desc') }}"
            >
                <x-icon name="list" />
            </button>
        @endif
    </div>
</div>
@if ($view === 'list')
    <div class="row row-cols-1 g-3 mb-3">
        @foreach ($contents as $content)
            <x-content.content-list
                :$content
                :show-drafts="$showDrafts ?? false"
            />
        @endforeach
    </div>
@else
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-3 row-cols-xl-3 row-cols-xxl-4 g-3 mb-3">
        @foreach ($contents as $content)
            <div class="col">
                <x-content.content-card
                    :$content
                    :show-drafts="$showDrafts ?? false"
                />
            </div>
        @endforeach
    </div>
@endif

{{ $contents->links() }}
