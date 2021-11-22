<!DOCTYPE html>
<html lang="@yield('language')" class="draft-html">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <base href="@yield('basePath', "/")">
    <link rel="stylesheet" type="text/css" href="{{mix('front.css')}}">
    @foreach($styles as $style)
        <link rel="stylesheet" type="text/css" href="{{$style}}">
    @endforeach
</head>
<body class="draft-resource">
    <div>
        <figure>
            <img src="/graphical/visibility_off-24px.svg" alt="{{trans('common.draft-eye-alt-text')}}">
        </figure>
        <h2>{{trans('common.content-cannot-be-displayed')}}</h2>
    </div>
</body>
</html>
