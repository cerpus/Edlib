@props(['content', 'version'])
<a {{ $attributes->merge([
    'href' => route('content.version-details', [$content, $version]),
    'hx-get' => route('content.preview', [$content, $version]),
    'hx-target' => '#previewModal',
    'data-bs-toggle' => 'modal',
    'data-bs-target' => '#previewModal',
]) }}>{{ $slot }}</a>
