<div class="d-sm-block position-absolute end-0 layout-toggle">
    <x-form method="POST" action="{{ route('content.layout') }}">
        <x-form.button
            class="fs-5 py-1 px-2 btn-secondary border-0"
            :title="$title"
            aria-description="{{ trans('messages.result-list-desc') }}"
        >
            {{ $slot }}
        </x-form.button>
    </x-form>
</div>
