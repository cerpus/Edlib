<input {{ $attributes->class([
    'form-check-input',
    'is-invalid' => $errors->any() && $errors->has($name),
])->merge([
    'type' => 'checkbox',
    'value' => '1',
    'checked' => isset($name) ? old($name) : false,
    'id' => $name ?? null,
]) }}>
