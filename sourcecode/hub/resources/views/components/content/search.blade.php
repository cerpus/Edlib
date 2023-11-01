{{-- TODO: hitting enter/the button should not reload --}}
<form action="" method="GET" class="row g-3 mb-3">
    <div class="mt-3">
        <div class="row g-3 align-items-center">
            <div class="col-8 col-md-5 col-lg-6">
                <label class="input-group">
                    <x-form.input
                        wire:model="query"
                        name="q"
                        type="search"
                        :value="$query"
                        :aria-label="trans('messages.search-query')"
                        placeholder="{{ trans('messages.type-to-search') }}"
                    />
                    <x-icon name="search" class="input-group-text" />
                </label>
            </div>

            <x-filter-desktop/>

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
</form>

<x-selected-filter-options/>

<x-mobile-filter-options />
