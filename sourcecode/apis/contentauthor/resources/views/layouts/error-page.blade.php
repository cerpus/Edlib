<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('error')</title>
    <link rel="stylesheet" href="{{ mix('css/error-page.css') }}">
</head>
<body>
<div class="container">
    <div class="content">
        <div class="title">@yield('error')</div>
        <div class="request-id">ID: {{ request()->header('X-Request-Id') }}</div>
        @if(config("app.debug", false) === true)
            <div class="errormessage exception">{{ $exception->getMessage() }}</div>
        @endif
    </div>
</div>
</body>
</html>
