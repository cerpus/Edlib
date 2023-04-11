@php use Illuminate\Support\Facades\Vite; @endphp
<svg {{ $attributes->except(['name', 'label'])->merge([
    'class' => 'bi',
    'role' => 'img',
    'width' => 16,
    'height' => 16,
    'fill' => 'currentColor',
    ...(isset($label) ? [
        'aria-label' => $label,
        'role' => 'img',
    ] : [
        'aria-hidden' => true,
    ]),
]) }}>
    <use xlink:href="{{ Vite::asset('node_modules/bootstrap-icons/bootstrap-icons.svg') }}#{{ $name }}" />
</svg>
