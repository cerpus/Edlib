@props(['filter'])
<div class="row pt-3 g-3">
    <label class="col-12" id="filterContentTypeLabel">
        {{ trans('messages.content-type') }}
        <span class="visually-hidden" id="filterContentTypeLabelText">
            {{ trans('messages.filter-content-types-desc') }}
        </span>
        <x-form.dropdown
            id="filterContentType"
            name="type[]"
            multiple
            :options="$filter->getContentTypeOptions(withExpectedHits: true)"
            :selected="$filter->getContentTypes()"
            class="filter-content-type"
            aria-labelledby="filterContentTypeLabelText"
        />
    </label>

    <label class="col-12 col-sm-6" id="filterLanguageLabel">
        {{ trans('messages.language') }}
        <span class="visually-hidden" id="filterLanguageLabelText">
            {{ trans('messages.filter-language') }}
        </span>
        <x-form.dropdown
            id="filterLanguage"
            name="language"
            :selected="$filter->getLanguage()"
            :options="$filter->getLanguageOptions(withExpectedHits: true)"
            aria-labelledby="filterLanguageLabelText"
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
