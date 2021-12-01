<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Article')</title>
    <link rel="stylesheet" href="{{ mix('css/content_explorer_bootstrap.css') }}">
    <link rel="stylesheet" href="{{ mix('css/h5picons.css') }}">
    <link rel="stylesheet" href="{{ mix('css/link.css') }}">
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Lato:400,700">
    @yield('customCSS')
</head>
<body>
<script>
    var iframeParent = function () {
        // Send 'resize' message to make parent container as large as we need
        var parent = window.parent;
        if (typeof parent !== 'undefined' && parent !== window) {
            var body = document.body;
            var html = document.documentElement;
            var height = Math.max(
                body.scrollHeight,
                body.offsetHeight,
                html.clientHeight,
                html.scrollHeight,
                html.offsetHeight
            );
            if (height > 0) {
                var data = {
                    context: 'h5p',
                    action: 'resize',
                    scrollHeight: height + 30
                };
                parent.postMessage(data, '*');
            }
        }
    };

    document.addEventListener("DOMContentLoaded", function (event) {
        // Respond to H5P resize events, do not relay.
        // Relay xAPI and result events to the parent.
        window.addEventListener("message", function (event) {
            if (event.data && event.data.context && event.data.context === 'h5p') {
                var action = event.data.action || '';
                switch (action) {
                    case 'resize':
                        if (event.data.context !== 'h5p') {
                            return; // Only handle h5p requests.
                        }

                        // Find out who sent the message
                        var iframe, iframes = document.getElementsByTagName('iframe');
                        for (var i = 0; i < iframes.length; i++) {
                            if (iframes[i].contentWindow === event.source) {
                                iframe = iframes[i];
                                break;
                            }
                        }
                        if (iframe !== null) {
                            iframe.height = event.data.scrollHeight;
                            iframe.frameBorder = 0;
                        }
                        iframeParent();
                        break;
                }
            }
        }, false);
    });

    window.addEventListener("load", function load(event) {
        window.removeEventListener("load", load, false); //remove listener, no longer needed
        iframeParent();
    });
</script>
<div class="linkContainer">
    @yield('content')
</div>
</body>
</html>
