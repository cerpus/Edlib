<form action="" method="GET" class="row g-3 mb-3">
    <div class="col-12 col-lg-6">

        <div class="input-group">
            <label class="input-group-text" for="q">
                <x-icon name="search" label="{{ trans('messages.search-query') }}"/>
            </label>

            <x-form.input
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
    </div>
</form>
