<nav class="navbar navbar-expand-md mb-3">
    <div class="container-md">
        <a href="{{ route('home') }}" class="navbar-brand" aria-hidden=true>
            <img
                src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/logo.png') }}"
                width="350"
                height="159"
                alt="{{ config('app.name') }}"
            >
        </a>

        <div class="collapse navbar-collapse d-lg-flex" id="global-nav-scroll">
            <ul class="nav nav-underline navbar-nav justify-content-center flex-grow-1">
                <li class="nav-item">
                    <a
                        href="{{ route('content.index') }}"
                        @class(['nav-link', 'active' => request()->routeIs('content.index')])
                    >
                        <svg width="16" height="19.5" viewBox="0 4 24 24" class="bi" >
                            <path d="M19.05 16.9C19.45 16.2 19.75 15.4 19.75 14.5C19.75 12 17.75 10 15.25 10C12.75 10 10.75 12 10.75 14.5C10.75 17 12.75 19 15.25 19C16.15 19 16.95 18.7 17.65 18.3L20.85 21.5L22.25 20.1L19.05 16.9ZM15.25 17C13.85 17 12.75 15.9 12.75 14.5C12.75 13.1 13.85 12 15.25 12C16.65 12 17.75 13.1 17.75 14.5C17.75 15.9 16.65 17 15.25 17ZM11.75 20V22C6.23 22 1.75 17.52 1.75 12C1.75 6.48 6.23 2 11.75 2C16.59 2 20.62 5.44 21.55 10H19.48C18.84 7.54 17.08 5.53 14.75 4.59V5C14.75 6.1 13.85 7 12.75 7H10.75V9C10.75 9.55 10.3 10 9.75 10H7.75V12H9.75V15H8.75L3.96 10.21C3.83 10.79 3.75 11.38 3.75 12C3.75 16.41 7.34 20 11.75 20Z" fill="currentColor"/>
                        </svg>
                        {{ trans('messages.explore') }}
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        href="{{ route('content.create') }}"
                        @class(['nav-link', 'active' => request()->routeIs('content.create')])
                    >
                        <x-icon name="pencil" class="me-1" />
                        {{ trans('messages.create') }}
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        href="{{ route('content.mine') }}"
                        @class(['nav-link', 'active' => request()->routeIs('content.mine')])
                    >
                        <x-icon name="person-lines-fill" class="me-1" />
                        {{ trans('messages.my-content') }}
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav justify-content-end">
                @auth
                    <li class="nav-item dropdown">
                        <button
                            class="nav-link dropdown-toggle"
                            aria-expanded="false"
                            data-bs-toggle="dropdown"
                            type="button"
                            aria-label="{{ trans('messages.toggle-menu') }}"
                        >
                            {{ auth()->user()->name }}
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a
                                    href="{{ route('user.my-account') }}"
                                    class="dropdown-item"
                                >
                                    <x-icon name="person-fill-gear" class="me-2" />
                                    {{ trans('messages.my-account') }}
                                </a>
                            </li>
                            <li>
                                <a
                                    href="{{ route('user.preferences') }}"
                                    class="dropdown-item"
                                >
                                    <x-icon name="wrench" class="me-2" />
                                    {{ trans('messages.preferences') }}
                                </a>
                            </li>
                            @can('admin')
                                <li>
                                    <a
                                        href="{{ route('admin.index') }}"
                                        class="dropdown-item"
                                    >
                                        <x-icon name="gear-fill" class="me-2" />
                                        {{ trans('messages.admin-home') }}
                                    </a>
                                </li>
                            @endcan
                            <li>
                                <form action="{{ route('log_out') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item">
                                        <x-icon name="box-arrow-right" class="me-2" />
                                        {{ trans('messages.log-out') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    @can('login')
                        <li class="nav-item">
                            <a
                                href="{{ route('login') }}"
                                @class(['nav-link', 'active' => request()->routeIs('login')])
                            >
                                {{ trans('messages.log-in') }}
                            </a>
                        </li>
                    @endcan

                    @can('register')
                        <li class="nav-item">
                            <a
                                href="{{ route('register') }}"
                                @class(['nav-link', 'active' => request()->routeIs('register')])
                            >
                                {{ trans('messages.sign-up') }}
                            </a>
                        </li>
                    @endcan
                @endauth
            </ul>
        </div>
    </div>
</nav>