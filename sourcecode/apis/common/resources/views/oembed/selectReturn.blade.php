<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Returning content back to app</title>
</head>
<body>
<script>
    (function () {
        var contentType = "{{ $contentType }}";
        var h5pId = "{{ $h5pId }}";
        var embedId = "{{ $embedId }}";
        var oembedUrl = "{{ $oembedUrl }}";

        var message = {
            'type': contentType,
            'h5p_id': h5pId,
            'embed_id': embedId,
            'oembed_url': oembedUrl
        };

        window.parent.postMessage(message, '*');
    })();
</script>
</body>
</html>
