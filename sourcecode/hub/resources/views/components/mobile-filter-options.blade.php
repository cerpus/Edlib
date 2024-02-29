<div class="offcanvas offcanvas-bottom" id="offcanvasBottomMobile" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="offcanvas-body">
        <div class="mb-2">
            <button
                class="btn btn-secondary border-0"
                type="button"
                data-bs-dismiss="offcanvas"
                aria-label="{{ trans('messages.back') }}"
            >
                <x-icon name="arrow-return-left" class="me-1" />
            </button>
        </div>

        <form
            hx-get="{{\Illuminate\Support\Facades\URL::current()}}"
            hx-target="#content"
            hx-include="this,#topFilterQuery,#topFilterSort"
            hx-validate="true"
            hx-replace-url="true"
        >
            <x-filter :$language :$languageOptions />

            <div class="text-center">
                <button
                    class="btn btn-primary d-md-none mt-5"
                    type="submit"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasBottomMobile"
                    aria-controls="offcanvasBottomMobile"
                    aria-label="{{ trans('messages.search-results') }}"
                >
                    {{ trans('messages.search-results-mobile') }}
                </button>
            </div>
        </form>
    </div>
</div>
