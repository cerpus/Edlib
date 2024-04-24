@props(['filter'])

<form
    hx-get="{{ url()->current() }}"
    hx-target="#content"
    hx-trigger="input from:find #filterQuery delay:500ms, change from:find #filterSort delay:500ms,change from:find #filterLanguage delay:500ms, change"
    hx-params="not search_terms"
    hx-validate="true"
    hx-replace-url="true"
    hx-sync="closest form:abort"
    hx-swap="innerHTML"
    hx-indicator="#content-loading"
>
    <div class="row">
        <div class="col">
            <label class="input-group">
                <x-form.input
                    id="filterQuery"
                    name="q"
                    type="search"
                    :value="$filter->getQuery()"
                    :aria-label="trans('messages.search-query')"
                    placeholder="{{ trans('messages.type-to-search') }}"
                />
                <x-icon name="search" class="input-group-text" />
            </label>
        </div>

        <div class="col-auto">
            <button
                id="filterToggleButton"
                class="btn btn-outline-secondary filter-button collapsed position-relative"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapseFilter"
                aria-controls="collapseFilter"
                aria-expanded="false"
            >
                <x-icon name="filter"/>
                <span class="d-none d-sm-inline-block">
                    {{ trans('messages.filter') }}
                    <div
                        id="filterActiveCountLabel"
                        @class([
                            "position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-primary filter-button-count",
                            "visually-hidden" => $filter->activeCount() === 0,
                        ])
                    >
                        <span class="visually-hidden">{{ trans('messages.filter-active') }}</span>
                        <span id="filterActiveCount">{{ $filter->activeCount() }}</span>
                    </div>
                </span>
            </button>
        </div>
    </div>

    <div class="container-fluid my-1 collapse" id="collapseFilter">
        <x-filter.collapsible :$filter />
    </div>
</form>

<script nonce="{{\Illuminate\Support\Facades\Vite::cspNonce()}}">
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll("#collapseFilter select").forEach(elm => {
            elm.choices = new ChoicesJs(elm, {
                addItems: true,
                allowHTML: false,
                duplicateItemsAllowed: false,
                itemSelectText: '',
                noChoicesText: '{{ trans('messages.filter-dropdown-no-choices') }}',
                noResultsText: '{{ trans('messages.filter-dropdown-no-results') }}',
                paste: false,
                removeItemButton: elm.getAttribute('multiple') !== null,
                removeItems: elm.getAttribute('multiple') !== null,
                resetScrollPosition: false,
                searchEnabled: elm.getAttribute('data-choicesjs-search-enabled') !== 'false',
                searchResultLimit: 100,
                maxItemCount: 10,
                shouldSort: elm.getAttribute('data-choicesjs-should-sort') !== 'false',
                maxItemText: maxItemCount => `{{ trans('messages.filter-dropdown-max-items') }}`,
                labelId: elm.getAttribute('aria-labelledby'),
            });
        });
    });
</script>
