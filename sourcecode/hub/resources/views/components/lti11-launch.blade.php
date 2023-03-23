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
    <input type="hidden" name="lti_message_type" value="basic-lti-launch-request">
    <input type="hidden" name="lti_version" value="LTI-1p0">
    <input type="hidden" name="ext_preview" value="{{ $preview }}">
    <input type="hidden" name="launch_presentation_locale" value="{{ $locale }}">
    <input type="hidden" name="launch_presentation_document_target" value="iframe">
    <input type="hidden" name="launch_presentation_width" value="{{ $width }}">
    <input type="hidden" name="launch_presentation_height" value="{{ $height }}">
</form>
