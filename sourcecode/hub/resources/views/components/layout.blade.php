<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        {{ $head ?? '' }}
    </head>
    <body>
        <header>
            <h1>{{ $title ?? config('app.name') }}</h1>

            @auth
                <p>Logged in as <strong>{{ auth()->id() }}</strong>.
                @can('admin')
                    <a href="{{ route('admin.index') }}">{{ trans('messages.admin-home') }}</a>
                @endcan
                <form action="{{ route('log_out') }}" method="POST">
                    @csrf
                    <button>Log out</button>
                </form>
            @else
                <p><a href="{{ route('login') }}">{{ trans('messages.log-in') }}</a></p>
            @endauth

            <hr>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer>
            @env('local')
                <p><a href="{{ route('telescope') }}">Debug</a></p>
            @endenv
        </footer>
    </body>
</html>
