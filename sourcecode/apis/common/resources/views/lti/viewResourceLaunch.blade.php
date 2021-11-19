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
</body>
</html>
