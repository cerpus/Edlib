@props(['filter'])
<div class="row py-3 g-3">
    <label class="col-12">
        {{ trans('messages.content-type') }}
        <span class="visually-hidden" id="filterContentTypeLabel">
            {{ trans('messages.filter-content-types-desc') }}
        </span>
        <x-form.dropdown
            id="filterContentType"
            name="type[]"
            multiple
            :options="$filter->getContentTypeOptions()"
            :selected="$filter->getContentTypes()"
            class="filter-content-type"
            aria-labelledby="filterContentTypeLabel"
        />
    </label>

    <label class="col-12 col-sm-6">
        {{ trans('messages.language') }}
        <span class="visually-hidden" id="filterLanguageLabel">
            {{ trans('messages.filter-language') }}
        </span>
        <x-form.dropdown
            id="filterLanguage"
            name="language"
            :selected="$filter->getLanguage()"
            :options="$filter->getLanguageOptions()"
            :emptyOption="trans('messages.filter-language-all')"
            aria-labelledby="filterLanguageLabel"
            data-choicesjs-should-sort="false"
        />
    </label>

    <label class="col-12 col-sm-6" id="filterSortLabel">
        {{ trans('messages.sort-by') }}
        <x-form.dropdown
            id="filterSort"
            name="sort"
            :selected="$filter->getSortBy()"
            aria-labelledby="filterSortLabel"
            :options="$filter->getSortOptions()"
            data-choicesjs-search-enabled="false"
        />
    </label>

    <div class="col-12 d-flex justify-content-end">
        <a
            id="filterClearButton"
            @class(["btn btn-outline-secondary", "disabled" => empty($filter->activeCount())])
            role="button"
            href="{{ url()->current() }}"
            aria-disabled="{{empty($filter->activeCount()) ? "true" : "false"}}"
        >
            {{ trans('messages.filter-clear') }}
        </a>
    </div>
</div>
