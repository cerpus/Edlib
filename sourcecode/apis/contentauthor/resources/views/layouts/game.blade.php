<!DOCTYPE html>
<html lang="@yield('language')">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="robots" content="noindex,indexifembedded">
    <base href="@yield('basePath', "/")">
@stack('css')
@stack('linked')
</head>
<body>
@yield('content')
@stack('js')

</body>
</html>
