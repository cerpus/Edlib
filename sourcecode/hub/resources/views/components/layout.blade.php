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

    <body @class(['body-nav-margin' => $nav, 'd-flex', 'flex-column', 'vh-100'])>
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

        <div class="container-md mb-4">
            <div class="row">
                <main @class(['col-12', 'col-lg-9' => isset($sidebar)])>
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

        <footer class="bg-body-tertiary text-body-secondary p-3 border-top border-secondary-subtle mt-auto">
            <div class="container py-3">
                <div class="row">
                    <div class="col-12 col-md-4 col-lg-6">
                        <p>
                            <img
                                src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/logo.png') }}"
                                height="47"
                                alt="Edlib"
                                class="d-block"
                            >
                        </p>

                        <p>{!! trans('messages.about-edlib', [
                            'site' => config('app.name'),
                            'edlib' => '<a href="https://docs.edlib.com/" class="link-body-emphasis">Edlib</a>',
                        ]) !!}</p>
                    </div>

                    <div class="col-6 col-md-4 col-lg-3">
                        <h2 class="fs-5">Edlib</h2>

                        <ul class="list-unstyled">
                            <li><x-layout.footer-link href="https://docs.edlib.com/">{{ trans('messages.documentation') }}</x-layout.footer-link></li>
                            <li><x-layout.footer-link href="https://github.com/cerpus/Edlib">{{ trans('messages.github') }}</x-layout.footer-link></li>
                            <li><x-layout.footer-link href="https://www.facebook.com/cerpus/">{{ trans('messages.facebook') }}</x-layout.footer-link></li>
                            <li><x-layout.footer-link href="https://twitter.com/edlibopensource">{{ trans('messages.twitter') }}</x-layout.footer-link></li>
                        </ul>
                    </div>

                    <div class="col-6 col-md-4 col-lg-3">
                        <h2 class="fs-5">{{ config('app.name') }}</h2>

                        <ul class="list-unstyled">
                            @if (config('app.contact-url'))
                                <li><x-layout.footer-link href="{{ config('app.contact-url') }}">{{ trans('messages.contact-us') }}</x-layout.footer-link></li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </footer>

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
