<nav class="navbar fixed-bottom d-sm-flex d-md-none navbar-bottom">
    <div class="container-fluid">
        <div id="global-nav-fixed" class="d-sm-flex flex-grow-1">
            <ul class="nav nav-underline navbar-nav justify-content-evenly flex-grow-1 flex-row">
                <li class="nav-item">
                    <a
                        href="{{ route('content.index') }}"
                        class="nav-link @if(request()->routeIs('content.index')) active @endif"
                        title="{{ trans('messages.explore') }}"
                    >
                        <svg width="48" height="40" viewBox="0 0 24 24" class="bi" >
                            <path
                                d="M19.05 16.9C19.45 16.2 19.75 15.4 19.75 14.5C19.75 12 17.75 10 15.25 10C12.75 10 10.75 12 10.75 14.5C10.75 17 12.75 19 15.25 19C16.15 19 16.95 18.7 17.65 18.3L20.85 21.5L22.25 20.1L19.05 16.9ZM15.25 17C13.85 17 12.75 15.9 12.75 14.5C12.75 13.1 13.85 12 15.25 12C16.65 12 17.75 13.1 17.75 14.5C17.75 15.9 16.65 17 15.25 17ZM11.75 20V22C6.23 22 1.75 17.52 1.75 12C1.75 6.48 6.23 2 11.75 2C16.59 2 20.62 5.44 21.55 10H19.48C18.84 7.54 17.08 5.53 14.75 4.59V5C14.75 6.1 13.85 7 12.75 7H10.75V9C10.75 9.55 10.3 10 9.75 10H7.75V12H9.75V15H8.75L3.96 10.21C3.83 10.79 3.75 11.38 3.75 12C3.75 16.41 7.34 20 11.75 20Z"
                                fill="currentColor"
                            />
                        </svg>
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        href="{{ route('content.create') }}"
                        class="nav-link h1 @if(request()->routeIs('content.create')) active @endif"
                        title="{{ trans('messages.create') }}"
                    >
                        <x-icon name="pencil" class="me-1 display-6" />
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        href="{{ route('content.mine') }}"
                        class="nav-link @if(request()->routeIs('content.mine')) active @endif"
                        title="{{ trans('messages.my-content') }}"
                    >
                        <x-icon name="person-lines-fill" class="me-1 display-6" />
                    </a>
                </li>
                @auth
                    <li class="nav-item dropup">
                        <a
                            href="#"
                            class="nav-link dropdown-toggle"
                            aria-expanded="false"
                            data-bs-toggle="dropdown"
                            role="button"
                        >
                            <x-icon name="list" class="me-2 display-6" />
                        </a>

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
