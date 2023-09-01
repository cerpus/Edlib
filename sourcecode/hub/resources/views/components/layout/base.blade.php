<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-bs-theme="{{ request()->user()->theme ?? app()->make(\App\Configuration\Themes::class)->getDefault() }}"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.scss', 'resources/js/app.js'])
        @livewireStyles(['nonce' => \Illuminate\Support\Facades\Vite::cspNonce()])
        {{ $head ?? '' }}
    </head>

    <body class="@if ($nav ?? true) body-nav-margin @endif">
        @if ($nav ?? true)
            <x-navbar.navbar-top />
        @endif

        @if (session()->has('alert'))
            {{-- TODO: make floating so the page content doesn't bounce around --}}
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('alert') }}
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="{{ trans('messages.close') }}"
                ></button>
            </div>
        @endif

        {{ $slot }}

        @if ($nav ?? true)
            <x-navbar.navbar-bottom />
        @endif

        @env('local')
            <div class="position-fixed btn-debug">
                <a href="{{ route('telescope') }}" title="Laravel Telescope">
                    <x-icon name="bug" />
                </a>
            </div>
        @endenv
        @livewireScripts(['nonce' => \Illuminate\Support\Facades\Vite::cspNonce()])
    </body>
</html>
