@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Vite;

    $uniqueId = Str::uuid()->toString();
    $method ??= 'GET';
    $target ??= '_self';

    // <form> action cannot have query params if the method is GET
    // These have to be sent as hidden inputs.
    if (strtoupper($method) === 'GET') {
        $urlParts = explode('?', $url, 2);
        $url = $urlParts[0];
        if (isset($urlParts[1])) {
            parse_str($urlParts[1], $urlParams);
            $parameters = array_replace($parameters ?? [], $urlParams);
        }
    }
@endphp

<form
    action="{{ $url }}"
    method="{{ $method }}"
    name="launch-form-{{ $uniqueId }}"
    target="{{ $target }}"
>
    @foreach($parameters as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach

    <noscript>
        <button class="btn btn-primary">{{ trans('messages.continue') }}</button>
    </noscript>
</form>

<script nonce="{{ Vite::cspNonce() }}">
    document.forms['launch-form-' + @json($uniqueId)].submit();
</script>
