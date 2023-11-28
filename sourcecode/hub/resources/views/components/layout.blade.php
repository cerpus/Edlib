@props(['showHeader' => true, 'nav' => true])
<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-bs-theme="{{ auth()->user()?->theme ?? app()->make(\App\Configuration\Themes::class)->getDefault() }}"
    data-session-scope="{{ app()->make(\App\Support\SessionScope::class)->getToken(request()) }}"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.scss', 'resources/js/app.js'])
        @livewireStyles(['nonce' => \Illuminate\Support\Facades\Vite::cspNonce()])
        {{ $head ?? '' }}
    </head>

    <body class="@if ($nav) body-nav-margin @endif">
        @if ($nav)
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

        <div class="container-md">
            <div class="row">
                <main class="col-12 @isset($sidebar) col-lg-9 @endisset">
                    @if ($showHeader)
                        <header>
                            <h1 class="fs-2">{{ $title }}</h1>
                        </header>
                    @endif

                    {{ $slot }}
                </main>

                @isset ($sidebar)
                    <aside class="col-12 col-lg-3">
                        {{ $sidebar }}
                    </aside>
                @endisset
            </div>

            @if (
                request()->hasPreviousSession() &&
                request()->session()->has('lti') &&
                (auth()->user()?->debug_mode ?? app()->hasDebugModeEnabled())
            )
                <details>
                    <summary>LTI launch details</summary>
                    <x-lti-debug :parameters="request()->session()->get('lti')" />
                </details>
            @endif
        </div>

        @if ($nav)
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
