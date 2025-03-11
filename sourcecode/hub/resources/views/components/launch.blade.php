<iframe {{ $attributes->except(['direct', 'forwards-resize-messages'])->merge([
    'src' => $direct ? 'about:blank' : $url,
    'name' => 'launch-frame-' . $uniqueId,
    'data-log-to' => $logTo,
    'frameborder' => '0',
    'width' => $width,
    'height' => $height,
    'allowfullscreen' => 'allowfullscreen',
    'allow' => 'fullscreen *; geolocation *; microphone *; camera *; midi *; encrypted-media *',
])->class([
    'lti-launch',
    'd-block',
    'forwards-resize-messages' => $forwardsResizeMessages,
]) }}></iframe>

@if ($direct)
    <x-self-submitting-form
        :action="$url"
        :method="$method"
        :parameters="$parameters"
        target="launch-frame-{{ $uniqueId }}"
    />
@endif
