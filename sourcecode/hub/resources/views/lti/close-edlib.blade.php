{{-- TODO: use same base template as everything else --}}
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Closing Edlib</title>
    </head>

    <body>
        {{-- TODO: don't use inline scripts --}}
        <script>
            parent.postMessage('close', '*');
        </script>
    </body>
</html>
