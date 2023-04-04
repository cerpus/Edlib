<form action="" method="GET" class="mb-3">
    <div class="row">
        <label class="col-auto col-form-label" for="q">
            {{ trans('messages.search') }}
        </label>
        <div class="col-auto">
            <x-form.input
                name="q"
                type="search"
                :value="$query"
                :aria-label="trans('messages.search')"
            />
        </div>
        <div class="col-auto">
            <x-form.button
                class="btn-secondary"
                :aria-label="trans('messages.search')"
            >
                🔍 {{ trans('messages.search') }}
            </x-form.button>
        </div>
    </div>
</form>
