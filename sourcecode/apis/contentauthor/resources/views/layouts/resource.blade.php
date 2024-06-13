<!DOCTYPE html>
@php
    $locale = strtolower(str_replace('_', '-', Session::get('locale', App::getLocale())));
    if (strlen($locale) > 2 && !str_contains($locale, '-')) {
        $locale = Iso639p3::code2letters($locale);
    }
@endphp
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ mix('css/content_explorer_bootstrap.css') }}">
    <link rel="stylesheet" href="{{ mix('css/font-awesome.css') }}">
    <script src="https://code.jquery.com/jquery.min.js"></script>
    <link rel="stylesheet" href="{{ mix('css/admin.css') }}">
    <link href='//fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
    <script src="{{ mix('js/bootstrap.js') }}"></script>
    <script src="{{ asset('js/jsrequestintercept.js') }}"></script>
    @stack("css")
</head>
<body id="theBody" onunload="unlock();">
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
  window.unlock = window.unlock || function () {};
</script>
</body>
</html>
