<!DOCTYPE html>
<html lang="{{$language}}" class="h5p-admin-preview">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link media="all" type="text/css" rel="stylesheet" href="{{ mix('css/h5p-core.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ mix('css/h5pcss.css') }}">
    @if($inlineStyle)
        <style>{!! $inlineStyle !!}</style>
    @endif
    @foreach( $styles as $css)
        {!! HTML::style($css) !!}
    @endforeach
    {!! HTML::script('https://code.jquery.com/jquery-1.12.4.min.js') !!}
    <script type="text/x-mathjax-config">
        // When MathJax is done, check if a resize of the container is required
        MathJax.Hub.Register.StartupHook("End", function () {
            if (window.parent && document.documentElement.scrollHeight > document.documentElement.clientHeight) {
                window.parent.postMessage({
                  context: 'h5p',
                  action: 'hello'
                }, '*');
            }
        });
    </script>
</head>
<body>
    <div class="h5p-preview">
        {!! $embed !!}
    </div>
    {!! $config !!}

    <script src="{{ mix('js/h5p-core-bundle.js') }}"></script>
    @foreach( $jsScripts as $js)
        {!! HTML::script($js) !!}
    @endforeach
    <script>
        H5P.jQuery.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': H5P.jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
</body>
</html>
