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

{{-- TODO: no inline scripts, redirect to the new content --}}
<script>
    window.addEventListener('message', (event) => {
        if (event.data === 'close') {
            alert('Edlib should close, now');
        }
    });
</script>
