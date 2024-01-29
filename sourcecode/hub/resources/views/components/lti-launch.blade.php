<iframe {{ $attributes->merge([
    'src' => $iframeUrl,
    'name' => 'lti-launch-' . $uniqueId,
    'width' => $width ?? 640,
    'height' => $height ?? 480,
    'class' => 'lti-launch d-block',
    'data-log-to' => $logTo,
]) }}></iframe>
