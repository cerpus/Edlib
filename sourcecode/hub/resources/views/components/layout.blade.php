<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.scss', 'resources/js/app.js'])
        {{ $head ?? '' }}
    </head>

    <body>
        <nav class="navbar navbar-expand-lg bg-dark mb-3" data-bs-theme="dark">
            <div class="container-md">
                <a href="{{ route('home') }}" class="navbar-brand">
                    {{ config('app.name') }}
                </a>

                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#global-nav-scroll"
                    aria-controls="global-nav-scroll"
                    aria-expanded="false"
                    aria-label="{{ trans('messages.toggle-menu') }}"
                >
                    <span class="navbar-toggler-icon" aria-hidden="true"></span>
                </button>

                <div class="collapse navbar-collapse d-lg-flex" id="global-nav-scroll">
                    <ul class="navbar-nav justify-content-center flex-grow-1">
                        <li class="nav-item">
                            <a
                                href="{{ route('content.mine') }}"
                                class="nav-link @if(request()->routeIs('content.mine')) active @endif"
                            >
                                {{ trans('messages.my-content') }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a
                                href="{{ route('content.index') }}"
                                class="nav-link @if(request()->routeIs('content.index')) active @endif"
                            >
                                {{ trans('messages.shared-content') }}
                            </a>
                        </li>

                        @env('local')
                            <li class="nav-item">
                                <a href="{{ route('telescope') }}" class="nav-link">Debug</a>
                            </li>
                        @endenv
                    </ul>

                    <ul class="navbar-nav justify-content-end">
                        @auth
                            <li class="nav-item dropdown">
                                <a
                                    href="#"
                                    class="nav-link dropdown-toggle"
                                    aria-expanded="false"
                                    data-bs-toggle="dropdown"
                                >
                                    {{ auth()->user()->name }}
                                </a>

                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a
                                            href="{{ route('user.preferences') }}"
                                            class="dropdown-item">
                                            {{ trans('messages.preferences') }}
                                        </a>
                                    </li>
                                    @can('admin')
                                        <li>
                                            <a
                                                href="{{ route('admin.index') }}"
                                                class="dropdown-item"
                                            >
                                                {{ trans('messages.admin-home') }}</a>
                                        </li>
                                    @endcan
                                    <li>
                                        <form action="{{ route('log_out') }}" method="POST">
                                            @csrf
                                            <button class="dropdown-item">
                                                {{ trans('messages.log-out') }}
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a
                                    href="{{ route('login') }}"
                                    class="nav-link @if(request()->routeIs('login')) active @endif"
                                >
                                    {{ trans('messages.log-in') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a
                                    href="{{ route('register') }}"
                                    class="nav-link @if(request()->routeIs('register')) active @endif"
                                >
                                    {{ trans('messages.sign-up') }}
                                </a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>

        <main class="container">
            <header>
                <h1>{{ $title }}</h1>
            </header>

            {{ $slot }}
        </main>
    </body>
</html>
