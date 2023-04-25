<input {{ $attributes->except('required')
    ->class([
        'form-control',
        'is-invalid' => $errors->any() && $errors->has($name),
    ])
    ->merge([
        'type' => 'text',
        'id' => $name,
        'value' => $value ?? old($name),
        'required' => (bool) ($required ?? false),
    ]) }}
>
