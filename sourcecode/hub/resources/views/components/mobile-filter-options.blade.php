<div class="offcanvas offcanvas-bottom" id="offcanvasBottomMobile" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="offcanvas-body">
        <div class="mb-2">
            <button
                id="backButton"
                class="btn btn-secondary border-0"
                type="button"
                data-bs-dismiss="offcanvas"
                aria-label="{{ trans('messages.back') }}"
            >
                <x-icon name="arrow-return-left" class="me-1" />
            </button>
        </div>

        <x-filter/>

        <div class="text-center">
            <button
                id="showResultsButton"
                class="btn btn-primary d-md-none mt-5"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasBottomMobile"
                aria-controls="offcanvasBottomMobile"
                aria-label="{{ trans('messages.search-results') }}"
            >
                {{ trans('messages.search-results-mobile', ['num' => 378]) }}
            </button>
        </div>

    </div>
</div>
