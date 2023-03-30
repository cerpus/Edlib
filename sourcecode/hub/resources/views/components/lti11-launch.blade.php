<iframe
    src=""
    name="lti-launch-{{ $uniqueId }}"
    width="{{ $width ?? 640 }}"
    height="{{ $height ?? 480 }}"
></iframe>

<form
    action="{{ $launch->getRequest()->getUrl() }}"
    method="{{ $launch->getRequest()->getMethod() }}"
    class="auto-submit"
    target="{{ 'lti-launch-'.$uniqueId }}"
>
    {!! $launch->getRequest()->toHtmlFormInputs() !!}
</form>
