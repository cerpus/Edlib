@props(['content' => null, 'version' => null, 'detailsUrl' => null, 'previewUrl' => null])
<a {{ $attributes->merge([
    'href' => $detailsUrl ?: route('content.version-details', [$content, $version]),
    'hx-get' => $previewUrl ?: route('content.preview', [$content, $version]),
    'hx-target' => '#modal-container',
    'hx-swap' => 'beforeend',
    'data-modal' => 'true',
]) }}>{{ $slot }}</a>
