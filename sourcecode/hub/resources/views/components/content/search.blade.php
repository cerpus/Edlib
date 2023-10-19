{{-- TODO: hitting enter/the button should not reload --}}
<form action="" method="GET" class="row g-3 mb-3">
    <div class="col-12 col-lg-6">
        <div class="input-group">
            <label class="input-group-text" for="q">
                <x-icon name="search" label="{{ trans('messages.search-query') }}"/>
            </label>
            <x-form.input
                wire:model="query"
                name="q"
                type="search"
                :value="$query"
                :aria-label="trans('messages.search')"
            />
        </div>
    </div>
    <div class="col-auto">
        <x-form.button
            class="btn-secondary"
            :aria-label="trans('messages.search')"
        >
            <x-icon name="search" />
            {{ trans('messages.search') }}
        </x-form.button>

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

<x-mobile-filter-options />
