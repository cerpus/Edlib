<iframe
    src=""
    name="lti-launch-{{ $uniqueId }}"
    width="{{ $width }}"
    height="{{ $height }}"
></iframe>

<form
    action="{{ $launchUrl }}"
    method="POST"
    class="auto-submit"
    target="{{ 'lti-launch-'.($uniqueId) }}"
>
    {!! $oauth1Request->toHtmlFormInputs() !!}
</form>
