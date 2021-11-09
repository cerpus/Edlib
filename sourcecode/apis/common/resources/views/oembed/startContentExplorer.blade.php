<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Edlib2</title>
    <style>
        html, body, iframe {
            width: 100%;
            height: 100%;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }
    </style>
</head>
<body>
<iframe src="{{ $edlibContentExplorerIframeUrl }}" frameborder="0" id="edlib2-iframe"></iframe>
<script>
    const childWindow = document.getElementById('edlib2-iframe').contentWindow;
    window.addEventListener('message', message => {
        if (message.source !== childWindow) {
            return; // Skip message in this event listener
        }

        switch(message.data.messageType) {
            case "resourceSelected":
                var returnUrl = "{{ $returnUrl }}";
                window.location.replace(returnUrl.replace("--placeholder--", message.data.launch));
                break;
            case "closeEdlibModal":
                window.parent.postMessage(message.data, "*");
                break;
        }
    });
</script>
</body>
</html>
