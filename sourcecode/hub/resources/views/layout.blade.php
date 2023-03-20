<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', config('app.name'))</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @yield('head')
    </head>

    <body>
        <header>
            <h1>@yield('title', config('app.name'))</h1>
        </header>

        <main>
            @yield('content')
        </main>

        <footer>
            @env('local')
                <p><a href="{{ route('telescope') }}">Debug</a></p>
            @endenv
        </footer>
    </body>
</html>
