@props(['contents', 'filter', 'mine' => false, 'showDrafts' => false])

<div class="mt-3" id="content">
    <form
        class="row g-3 align-items-center"
        hx-get="{{ url()->current() }}"
        hx-target="#content"
        hx-trigger="keyup changed from:find #topFilterQuery delay:500ms, change from:find #topFilterLanguage, change from:find #topFilterSort"
        hx-validate="true"
        hx-replace-url="true"
        hx-sync="closest form:abort"
        hx-swap="outerHTML"
        hx-indicator="#content-loading"
    >
        <div class="col-8 col-md-5 col-lg-6">
            <label class="input-group">
                <x-form.input
                    id="topFilterQuery"
                    name="q"
                    type="search"
                    :value="$filter->getQuery()"
                    :aria-label="trans('messages.search-query')"
                    placeholder="{{ trans('messages.type-to-search') }}"
                    hx-trigger="keyup changed delay:500ms"
                />
                <x-icon name="search" class="input-group-text" />
            </label>
        </div>

        <div class="col-md-3 col-lg-3 d-md-block d-none">
            <x-form.dropdown
                id="topFilterLanguage"
                name="language"
                :selected="$filter->getLanguage()"
                :aria-label="trans('messages.filter-language')"
                :options="$filter->getLanguageOptions()"
                :emptyOption="trans('messages.filter-language-all')"
            />
        </div>

        <div class="col-md-4 col-lg-3 d-md-block d-none">
            <x-form.dropdown
                id="topFilterSort"
                name="sort"
                :selected="$filter->getSortBy()"
                :aria-label="trans('messages.last-changed')"
                :options="$filter->getSortOptions()"
            />
        </div>

        <div class="col-4 col-lg-3">
            <button
                id="filterButton"
                class="btn btn-secondary d-md-none"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasBottomMobile"
                aria-controls="offcanvasBottomMobile"
                aria-label="{{ trans('messages.filter') }}"
            >
                <x-icon name="filter" />
                {{ trans('messages.filter') }}
            </button>
        </div>
    </form>

    <div class="spinner-border text-info unhide-when-loading" role="status" id="content-loading">
        <span class="visually-hidden">{{ trans('messages.loading') }}</span>
    </div>

    {{--<x-selected-filter-options/>--}}

    <x-mobile-filter-options
        :language="$filter->getLanguage()"
        :languageOptions="$filter->getLanguageOptions()"
    />

    @unless ($contents->isEmpty())
        @if ($filter->getLayout() === 'grid')
            <x-content.grid-header :layout="$filter->getLayout()" :total="$contents->total()" />
            <x-content.grid :contents="$contents" :$showDrafts :titlePreviews="$filter->isTitlePreview()" />
        @else
            <x-content.list-header :layout="$filter->getLayout()" :total="$contents->total()" />
            <x-content.list :contents="$contents" :$showDrafts :titlePreviews="$filter->isTitlePreview()" />
        @endif

        <div hx-boost="true" hx-target="#content">
            {{ $contents->withQueryString()->links() }}
        </div>
    @else
        <x-big-notice>
            <x-slot:title>
                @if ($filter->hasQuery())
                    {{ trans('messages.no-results-found') }}
                @elseif ($mine)
                    {{ trans('messages.you-have-no-content-yet') }}
                @else
                    {{ trans('messages.no-content-created-yet') }}
                @endif
            </x-slot:title>

            <x-slot:description>
                @if ($filter->hasQuery())
                    {{ trans('messages.no-results-found-description') }}
                @elseif ($mine)
                    {{ trans('messages.you-have-no-content-yet-description') }}
                @else
                    {{ trans('messages.no-content-created-yet-description') }}
                @endif
            </x-slot:description>

            @if ($mine)
                <x-slot:actions>
                    <a href="{{ route('content.index') }}" class="btn btn-secondary">
                        {{ trans('messages.explore-content') }}
                    </a>

                    <a href="{{ route('content.create') }}" class="btn btn-primary">
                        {{ trans('messages.create-content') }}
                    </a>
                </x-slot:actions>
            @endif
        </x-big-notice>
    @endunless
</div>
