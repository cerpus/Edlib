<html>
<header>

</header>
<body>
<script>
    var parentWindow = window.parent;
    if (parentWindow !== 'undefined') {
        var ltiResource = {
            return_type: '{{ $inputs["return_type"] }}',
            url: '{{ $inputs["url"]}}',
            text: '{{ $inputs["text"] }}',
            resourceId: '{{ $resourceId }}'
        };
        parentWindow.postMessage(ltiResource, '*');
    } else {
        console.log('Unable to find parent iframe.');
    }
</script>
</body>
</html>
