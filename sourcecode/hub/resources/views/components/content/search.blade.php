{{-- TODO: hitting enter/the button should not reload --}}
<form action="" method="GET" class="row g-3 mb-3">
    <div class="mt-3">
        <div class="row g-3 align-items-center">
            <div class="col-8 col-md-5 col-lg-6">
                <div class="input-group search-container">
                    <x-form.input
                        wire:model="query"
                        name="q"
                        type="search"
                        :value="$query"
                        :aria-label="trans('messages.search')"
                        class="form-control border-0"
                        placeholder="Type to Search..."
                    />
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-transparent border-0" id="search-icon">
                            <i class="bi bi-search"></i>
                        </span>
                    </div>
                </div>
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

<x-search-query/>

<x-mobile-filter-options />
