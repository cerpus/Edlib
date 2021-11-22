<!DOCTYPE html>
<html lang="@yield('language')">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    @if (isset($jwtToken) && $jwtToken)
        <meta name="jwt" content="{{ $jwtToken }}"/>
    @endif
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="stylesheet" type="text/css" href="{{mix('game.css')}}">
    <base href="@yield('basePath', "/")">
@stack('css')
@stack('linked')
</head>
<body>
@include('fragments.draft-editor')
@yield('content')
@stack('js')

</body>
</html>
