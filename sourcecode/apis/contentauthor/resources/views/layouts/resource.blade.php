<!DOCTYPE html>
<html lang="{{Session::get('locale', 'en')}}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if (isset($jwtToken) && $jwtToken)
        <meta name="jwt" content="{{ $jwtToken }}"/>
    @endif
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ mix('content_explorer_bootstrap.css') }}">
    <link rel="stylesheet" href="{{ mix('font-awesome.css') }}">
    <link rel="stylesheet" href="{{ mix('react-components.css') }}">
    <link rel="stylesheet" href="{{ mix('admin.css') }}">
    <link href='//fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
    <script src="{{ mix('bootstrap.js') }}"></script>
    <script src="{{ mix('react-vendor.js') }}"></script>
    <script src="{{ mix('react-components.js') }}"></script>
    <script src="{{ mix('jwtclient.js') }}"></script>
    @stack("css")
</head>
<body id="theBody" onunload="unlock();" data-locale="{{ Session::get('locale', config('app.fallback_locale'))}}">
<div id="mainContainer">
    <div class="mainContent" id="mainContent"></div>
</div>
<script>
    const editorSetup = JSON.parse(@json($editorSetup ?? '{}'));
    const contentState = JSON.parse(@json($state ?? '{}'));
</script>
@stack('configJs')
@stack('js')
<script>
    (function () {
        this.jwt = this.sessionJwt = (function () {
            var optMeta = $('meta[name=jwt]').get(0);
            if (typeof (optMeta) !== 'undefined') {
                return $(optMeta).attr('content');
            }
        })();
        new XMLHttpRequestBeforeSend((function (xhr) {
            if (typeof (this.jwt) !== 'undefined' && this.jwt) {
                xhr.setRequestHeader('Authorization', 'Bearer ' + this.jwt);
            }
        }).bind(this));
        if (typeof (this.sessionJwt) !== 'undefined' && this.sessionJwt) {
            var jwtClient = new JWTClient((function (newJwt) {
                if (typeof (newJwt) !== 'undefined' && newJwt && newJwt !== this.sessionJwt) {
                    this.jwt = newJwt;
                    $.ajax({
                        'url': '/jwt/update',
                        'data': {
                            'jwt': newJwt
                        },
                        'method': 'POST',
                        'success': (function () {
                            this.sessionJwt = newJwt;
                        }).bind(this)
                    });
                }
            }).bind(this));
            setInterval(function () {
                jwtClient.requestUpdateIfExpiringInSeconds(60);
            }, 1000);
        }
    })();

    if (typeof unlock === 'undefined') {
        unlock = function () {
        };
    }
</script>
</body>
</html>
