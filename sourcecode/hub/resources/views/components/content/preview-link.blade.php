@props(['content' => null, 'version' => null, 'detailsUrl' => null, 'previewUrl' => null])
<a {{ $attributes->merge([
    'href' => $detailsUrl ?: route('content.version-details', [$content, $version]),
    'hx-get' => $previewUrl ?: route('content.preview', [$content, $version]),
    'hx-target' => '#previewModal',
    'data-bs-toggle' => 'modal',
    'data-bs-target' => '#previewModal',
]) }}>{{ $slot }}</a>
