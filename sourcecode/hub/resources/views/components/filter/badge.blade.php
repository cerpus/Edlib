@props([
    'value',
    'name',
    'checked' => false,
    'removable' => false,
])
<span
    @class([
        "badge border",
        "d-inline-flex",
        "align-items-center",
        "me-2",
        "mb-2",
        "filter-badge",
        "filter-selected" => $checked,
        "filter-focus" => !$removable,
    ])
    @if(!$removable)
        tabindex="0"
    @endif
>
    <label class="form-check-label w-100 text-wrap">
        <input
            type="checkbox"
            value="{{$value}}"
            class="d-none"
            aria-hidden="true"
            name="{{$name}}"
            @checked($checked)
        >
        {{ $slot }}
        @if($removable)
            <button
                type="submit"
                class="btn btn-close btn-sm"
                aria-label="{{ trans('messages.filter-remove') }}"
            ></button>
        @endif
    </label>
</span>
