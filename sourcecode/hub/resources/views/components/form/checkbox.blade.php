<input {{ $attributes->merge([
    'class' => 'form-check-input',
    'type' => 'checkbox',
    'value' => '1',
    'checked' => isset($name) ? old($name) : false,
    'id' => $name ?? null,
]) }}>
