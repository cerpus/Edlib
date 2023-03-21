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

            @auth
                <p>Logged in as <strong>{{ auth()->id() }}</strong>.
                <form action="{{ route('log_out') }}" method="POST">
                    @csrf
                    <button>Log out</button>
                </form>
            @endauth

            <hr>
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
