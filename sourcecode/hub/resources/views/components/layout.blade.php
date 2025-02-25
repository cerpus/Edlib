@props([
    'noHeader' => false,
    'noNav' => false,
    'noFooter' => false,
    'expand' => false,
    'title' => null,
    'current' => null,
])
<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-bs-theme="{{ auth()->user()?->theme ?? app()->make(\App\Configuration\Themes::class)->getDefault() }}"
    data-session-scope="{{ app()->make(\App\Support\SessionScope::class)->getToken(request()) }}"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="htmx-config" content="{{ json_encode([
            'inlineScriptNonce' => \Illuminate\Support\Facades\Vite::cspNonce(),
            'includeIndicatorStyles' => false,
        ]) }}">
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.scss', 'resources/js/app.js'])
        @if ($noindex)
            <meta name="robots" content="noindex">
        @endif
        {{ $head ?? '' }}
    </head>

    <body class="d-flex flex-column vh-100">
        @if (app()->isLocal() && config('telescope.enabled'))
            <a href="{{ route('telescope') }}" class="edlib-debug">
                <x-icon name="bug" label="Laravel Telescope" />
            </a>
        @endif

        @unless ($noNav)
            <x-layout.navbar :$current />
        @endunless

        @if (session()->has('alert'))
            <div class="toast-container position-fixed top-0 start-50 translate-middle-x mt-3 bg-info rounded">
                <div role="status" aria-live="polite" class="toast show" aria-atomic="true" data-bs-autohide="false">
                    <div class="d-flex">
                        <div class="toast-body w-100">
                            {{ session('alert') }}
                        </div>
                        <button
                            type="button"
                            class="btn-close m-2 align-self-start"
                            data-bs-dismiss="toast"
                            aria-label="{{ trans('messages.close') }}"
                        ></button>
                    </div>
                </div>
            </div>
        @endif

        <div @class(['container-md' => !$expand, 'flex-grow-1'])>
            <main class="h-100 d-flex flex-column">
                @unless ($noHeader)
                    <header @class(['container-md' => $expand])>
                        <h1 class="fs-2">{{ $title }}</h1>
                    </header>
                @endunless

                {{ $slot }}
            </main>

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

        @unless ($noFooter)
            <footer class="bg-body-tertiary text-body-secondary p-3 border-top border-secondary-subtle mt-3">
                <div class="container py-3">
                    <div class="row">
                        <div class="col-12 col-md-4 col-lg-6">
                            <p>
                                <span
                                    aria-label="{{ config('app.name') }}"
                                    class="edlib-logo edlib-logo-footer"
                                    role="img"
                                ></span>
                            </p>

                            <p>{!! trans('messages.about-edlib', [
                                'site' => config('app.name'),
                                'edlib' => '<a href="https://docs.edlib.com/" target="_blank" class="link-body-emphasis">Edlib</a>',
                            ]) !!}</p>
                        </div>

                        <div class="col-6 col-md-4 col-lg-3">
                            <h2 class="fs-5">Edlib</h2>

                            <ul class="list-unstyled">
                                <li><x-layout.footer-link href="https://docs.edlib.com/" target="_blank">{{ trans('messages.documentation') }}</x-layout.footer-link></li>
                                <li><x-layout.footer-link href="https://github.com/cerpus/Edlib" target="_blank">{{ trans('messages.github') }}</x-layout.footer-link></li>
                                <li><x-layout.footer-link href="https://www.facebook.com/cerpus/" target="_blank">{{ trans('messages.facebook') }}</x-layout.footer-link></li>
                                <li><x-layout.footer-link href="https://twitter.com/edlibopensource" target="_blank">{{ trans('messages.twitter') }}</x-layout.footer-link></li>
                            </ul>
                        </div>

                        <div class="col-6 col-md-4 col-lg-3">
                            <h2 class="fs-5">{{ config('app.name') }}</h2>

                            <ul class="list-unstyled">
                                @if (config('app.contact-url'))
                                    <li><x-layout.footer-link href="{{ config('app.contact-url') }}" target="_blank">{{ trans('messages.contact-us') }}</x-layout.footer-link></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        @endif

        {{-- Replacement confirmation modal for htmx:confirm event --}}
        <x-htmx-confirm-modal />

        <div id="modal-container"></div>

        <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
            const modalContainer = document.querySelector('#modal-container');
            modalContainer.addEventListener('hidden.bs.modal', () => {
                modalContainer.firstChild.remove();
            });
        </script>
    </body>
</html>
