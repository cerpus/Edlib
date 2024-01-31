@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Vite;

    $uniqueId = Str::uuid()->toString();
@endphp

<form
    action="{{ $url }}"
    method="{{ $method ?? 'GET' }}"
    name="launch-form-{{ $uniqueId }}"
    target="{{ $target ?? '_self' }}"
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
