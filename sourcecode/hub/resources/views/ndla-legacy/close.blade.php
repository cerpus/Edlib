<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="utf-8">
        <title>Closing Edlib</title>
    </head>
    <body>
        <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
            parent.postMessage({
                messageType: 'closeEdlibModal'
            }, '*');
        </script>
    </body>
</html>
