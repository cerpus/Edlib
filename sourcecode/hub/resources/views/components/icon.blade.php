<span {{ $attributes->except(['name', 'label'])->merge([
    'class' => 'bi bi-' . $name,
    ...(isset($label) ? [
        'aria-label' => $label,
        'role' => 'img',
    ] : [
        'aria-hidden' => 'true',
    ]),
]) }}></span>
