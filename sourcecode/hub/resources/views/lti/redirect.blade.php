@php use Illuminate\Support\Facades\Vite; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Redirecting</title>
        <style nonce="{{ Vite::cspNonce() }}">
            body { background: gray }
        </style>
    </head>

    <body>
        <form action="{{ $url }}" method="{{ $method }}" target="{{ $target ?? '_self' }}" name="launch">
            @foreach ($parameters ?? [] as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach

            <noscript>
                <button class="btn btn-primary">{{ trans('messages.continue') }}</button>
            </noscript>
        </form>

        <script nonce="{{ Vite::cspNonce() }}">
            document.forms.launch.submit();
        </script>
    </body>
</html>
