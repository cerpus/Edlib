<input
    name="{{ $name }}"
    type="{{ $type ?? 'text' }}"
    id="{{ $name }}"
    value="{{ $value ?? old($name) }}"
    class="form-control"
>
