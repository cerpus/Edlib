<!DOCTYPE html>
<html lang="{{$language}}" class="h5p-iframe">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,indexifembedded">
    <title>{{ $title }}</title>
    <link media="all" type="text/css" rel="stylesheet" href="{{ mix('css/h5p-core.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ mix('css/h5pcss.css') }}">
    @if($inlineStyle)
        <style>{!! $inlineStyle !!}</style>
    @endif
    @foreach( $styles as $css)
        {!! HTML::style($css) !!}
    @endforeach
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
    {!! $embed !!}
    {!! $config !!}

    <script src="{{ mix('js/h5p-core-bundle.js') }}"></script>
    @foreach( $jsScripts as $js)
        {!! HTML::script($js) !!}
    @endforeach
    {!! HTML::script('js/listener.js') !!}
    <script>
        window.H5P.jQuery.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': window.H5P.jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    @if(config('app.env') === 'production')
        {!! resolve(\App\Libraries\H5P\Interfaces\H5PAdapterInterface::class)->addTrackingScripts() !!}
    @endif
</body>
</html>
