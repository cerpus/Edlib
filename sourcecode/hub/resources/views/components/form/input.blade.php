<input {{ $attributes->except('required')
    ->class([
        'form-control',
        'is-invalid' => $errors->any() && $errors->has($name),
    ])
    ->except(['value'])
    ->merge([
        'type' => 'text',
        'id' => $name,
        'value' => old($name, $value ?? ''),
        'required' => (bool) ($required ?? false),
    ]) }}
>
