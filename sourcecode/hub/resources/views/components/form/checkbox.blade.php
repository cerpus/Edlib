<input {{ $attributes->class([
    'form-check-input',
    'is-invalid' => $errors->any() && $errors->has($name),
])->except(['checked'])->merge([
    'type' => 'checkbox',
    'value' => '1',
    'checked' => $errors->any() ? old($name) === ($value ?? '1') : ($checked ?? false),
    'id' => $name ?? null,
]) }}>
