<div class="mt-3">
    <div class="row g-3 align-items-center">
        <div class="col-8 col-md-5 col-lg-6">
            <label class="input-group">
                <x-form.input
                    wire:model.live.debounce="query"
                    name="q"
                    type="search"
                    :value="$query"
                    :aria-label="trans('messages.search-query')"
                    placeholder="{{ trans('messages.type-to-search') }}"
                />
                <x-icon name="search" class="input-group-text" />
            </label>
        </div>

        <div class="col-md-3 col-lg-3 d-md-block d-none">
            <x-form.dropdown
                wire:model.live="language"
                name="language"
                :selected="$language"
                :aria-label="trans('messages.filter-language')"
                :options="$languageOptions"
                :emptyOption="trans('messages.filter-language-all')"
            />
        </div>

        <div class="col-md-4 col-lg-3 d-md-block d-none">
            <x-form.dropdown
                wire:model.live="sortBy"
                name="sort"
                :selected="$sortBy"
                :aria-label="trans('messages.last-changed')"
                :options="$sortOptions"
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
    </div>
</div>

{{--<x-selected-filter-options/>--}}

<x-mobile-filter-options :$language :$languageOptions />
