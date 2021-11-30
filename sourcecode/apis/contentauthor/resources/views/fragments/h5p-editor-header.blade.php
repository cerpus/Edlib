<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ mix('css/font-awesome.css') }}">
    <link rel='stylesheet' href="//fonts.googleapis.com/css?family=Lato:400,700">
    @if(isset($styles))
        @foreach( $styles as $css)
            {!! HTML::style($css) !!}
        @endforeach
    @endif
    <link rel="stylesheet" href="{{ mix('css/react-app.css') }}">
    {!! HTML::script('https://code.jquery.com/jquery-1.11.3.min.js') !!}
</head>
<body id="theBody" onunload="unlock()">
