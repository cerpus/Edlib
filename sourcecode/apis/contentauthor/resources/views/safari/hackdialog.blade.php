<!DOCTYPE html>
<html>
<head>
    <title>Safari fiks</title>
</head>
<body>
    <script>
        window.opener.postMessage('ready', '*');
        window.close();
    </script>
</body>
</html>
