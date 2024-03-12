@props(['filter'])

<form
    class="row gx-3 align-items-center"
    hx-get="{{ url()->current() }}"
    hx-target="#content"
    hx-trigger="input from:find #topFilterQuery delay:500ms, change from:find #topFilterSort delay:500ms"
    hx-include="this, #filterSelectedForm"
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
            />
            <x-icon name="search" class="input-group-text" />
        </label>
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

    <div class="col-4 col-lg-3 d-md-none">
        <button
            id="filterButton"
            class="btn btn-secondary"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#offcanvasBottomMobile"
            aria-controls="offcanvasBottomMobile"
            aria-label="{{ trans('messages.filter') }}"
        >
            <x-icon
                class=""
                name="{{$filter->getLanguage() !== '' || count($filter->getContentTypes()) > 0 ? 'filter-circle-fill' : 'filter'}}"
            />
            {{ trans('messages.filter') }}
        </button>
    </div>
</form>

<x-filter.selected :$filter />
<x-filter.mobile :$filter />
