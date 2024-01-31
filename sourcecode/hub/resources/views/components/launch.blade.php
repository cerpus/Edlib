@php use Illuminate\Support\Str; @endphp

@props([
    'url' => 'about:blank',
    'method' => 'GET',
    'parameters' => [],
    'target' => '_self',
    'direct' => false,
    'width' => 640,
    'height' => 480,
    'logTo' => '',
    'uniqueId' => (string) Str::uuid(),
])

<iframe {{ $attributes->except('direct')->merge([
    'src' => $direct ? 'about:blank' : $url,
    'name' => 'launch-frame-' . $uniqueId,
    'width' => $width,
    'height' => $height,
    'class' => 'lti-launch d-block',
    'data-log-to' => $logTo,
    'frameborder' => '0',
]) }}></iframe>

@if ($direct)
    <x-self-submitting-form
        :action="$url"
        :method="$method"
        :parameters="$parameters"
        target="launch-frame-{{ $uniqueId }}"
    />
@endif
