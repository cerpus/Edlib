@props(['filter'])
<div class="row py-3 g-3">
    <label class="col-12" aria-description="{{trans('messages.filter-content-types-desc')}}">
        {{ trans('messages.content-type') }}
        <x-form.dropdown
            id="filterContentType"
            name="type[]"
            multiple
            :options="$filter->getContentTypeOptions()"
            :selected="$filter->getContentTypes()"
            class="filter-content-type"
        />
    </label>

    <label class="col-12 col-sm-6" aria-description="{{ trans('messages.filter-language') }}">
        {{ trans('messages.language') }}
        <x-form.dropdown
            id="filterLanguage"
            name="language"
            :selected="$filter->getLanguage()"
            :aria-label="trans('messages.filter-language')"
            :options="$filter->getLanguageOptions()"
            :emptyOption="trans('messages.filter-language-all')"
        />
    </label>

    <label class="col-12 col-sm-6">
        {{ trans('messages.sort-by') }}
        <x-form.dropdown
            id="filterSort"
            name="sort"
            :selected="$filter->getSortBy()"
            :aria-label="trans('messages.sort-by')"
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
