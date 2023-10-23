@php($view = 'grid')
<div class="row flex-row align-items-center">
    <div class="col fw-bold mb-1">
        {{ trans('messages.num-content-found', ['num' => $contents->total()]) }}
    </div>
    <div class="col-auto d-none d-sm-block">
        @if ($view === 'grid')
            <button
                type="button"
                class="btn p-0 fs-4"
                title="{{ trans('messages.result-list') }}"
                aria-description="{{ trans('messages.result-list-desc') }}"
            >
                <x-icon name="list" />
            </button>
        @else
            <button
                type="button"
                class="btn p-0 fs-4"
                title="{{ trans('messages.result-grid') }}"
                aria-description="{{ trans('messages.result-list-desc') }}"
            >
                <x-icon name="grid-fill" />
            </button>
        @endif
    </div>
</div>
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-3 row-cols-xl-3 row-cols-xxl-4 g-3 mb-3">
    @foreach ($contents as $content)
        <div class="col">
            @if ($view === 'grid')
                <x-content-card
                    :content="$content"
                    :show-drafts="$showDrafts ?? false"
                />
            @else
                {{-- ToDo: Create list view --}}
            @endif
        </div>
    @endforeach
</div>

{{ $contents->links() }}
<x-preview-modal />
