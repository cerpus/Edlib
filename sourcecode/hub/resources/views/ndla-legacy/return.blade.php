<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="utf-8">
        <title>Returning to app</title>
    </head>

    <body>
        <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
            parent.postMessage({
                type: {!! json_encode($type) !!},
                embed_id: {!! json_encode($embed_id) !!},
                oembed_url: {!! json_encode($oembed_url) !!},
            }, '*');
        </script>
    </body>
</html>
