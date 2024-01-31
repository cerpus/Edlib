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
        <x-self-submitting-form
            :url="$url"
            :method="$method ?? 'GET'"
            :parameters="$parameters ?? []"
            :target="$target ?? '_self'"
        />
    </body>
</html>
