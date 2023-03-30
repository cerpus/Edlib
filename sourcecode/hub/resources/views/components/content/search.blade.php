<form action="" method="GET">
    <p>
        <input
            type="search"
            name="q"
            value="{{ $query }}"
            placeholder="Keywords"
            aria-label="{{ trans('messages.search') }}"
        >
        <button aria-label="search">🔍 {{ trans('messages.search') }}</button>
    </p>
</form>
