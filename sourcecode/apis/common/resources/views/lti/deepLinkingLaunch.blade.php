<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lti launch</title>
    <style>
        body {
            padding: 0;
            margin: 0;
            height: 100vh;
        }

        iframe {
            height: 100%;
            width: 100%;
        }
    </style>
</head>
<body>
<iframe id="iframe" src="{{$iframeUrl}}" frameborder="0"></iframe>
<script>
    const childWindow = document.getElementById('iframe').contentWindow;
    window.addEventListener('message', message => {
        // Skip message not coming from edlib iframe
        if (message.source !== childWindow) {
            return;
        }

        switch (message.data.messageType) {
            case "resourceSelected":
                var returnUrl = new URL("{{$returnUrl}}");
                returnUrl.searchParams.set("resources", JSON.stringify(message.data.resources));
                window.location.replace(returnUrl);
                break;
        }
    });
</script>
</body>
</html>
