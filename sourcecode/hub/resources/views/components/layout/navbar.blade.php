@php use Illuminate\Support\Facades\Vite; @endphp

<nav class="navbar navbar-expand mb-3">
    <div class="container-md d-flex justify-content-between">
        <a href="{{ route('home') }}" class="navbar-brand">
            <img
                src="{{ Vite::asset('resources/images/logo.png') }}"
                alt="{{ config('app.name') }}"
                class="edlib-logo"
                height="80"
            >
        </a>

        <ul class="nav nav-underline navbar-nav flex-grow-1 justify-content-center">
            <li class="nav-item">
                <a
                    href="{{ route('content.index') }}"
                    @class(['nav-link', 'active' => request()->routeIs('content.index'), 'd-flex', 'gap-2'])
                >
                    <x-icon name="globe-americas" />
                    <span class="d-none d-sm-block">{{ trans('messages.explore') }}</span>
                </a>
            </li>

            <li class="nav-item">
                <a
                    href="{{ route('content.create') }}"
                    @class(['nav-link', 'active' => request()->routeIs('content.create'), 'd-flex', 'gap-2'])
                >
                    <x-icon name="pencil" class="me-1" />
                    <span class="d-none d-sm-block">{{ trans('messages.create') }}</span>
                </a>
            </li>

            <li class="nav-item">
                <a
                    href="{{ route('content.mine') }}"
                    @class(['nav-link', 'active' => request()->routeIs('content.mine'), 'd-flex', 'gap-2'])
                >
                    <x-icon name="person-lines-fill" />
                    <span class="d-none d-sm-block">{{ trans('messages.my-content') }}</span>
                </a>
            </li>
        </ul>

        <ul class="nav nav-underline navbar-nav">
            @auth
                <li class="nav-item dropdown">
                    <button
                        class="nav-link dropdown-toggle"
                        data-bs-toggle="dropdown"
                        type="button"
                        aria-expanded="false"
                        aria-label="{{ trans('messages.toggle-menu') }}"
                    >
                        <span class="d-inline-flex gap-2">
                            <x-icon name="person-circle" />
                            <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                        </span>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="nav-item d-md-none">
                            <span class="dropdown-item-text">{{ auth()->user()->name }}</span>
                        </li>
                        <li class="nav-item d-md-none">
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a
                                href="{{ route('user.my-account') }}"
                                class="dropdown-item gap-2 d-flex"
                            >
                                <x-icon name="person-fill-gear"/>
                                <span>{{ trans('messages.my-account') }}</span>
                            </a>
                        </li>
                        <li>
                            <a
                                href="{{ route('user.preferences') }}"
                                class="dropdown-item gap-2 d-flex"
                            >
                                <x-icon name="wrench" />
                                <span>{{ trans('messages.preferences') }}</span>
                            </a>
                        </li>
                        @can('admin')
                            <li>
                                <a
                                    href="{{ route('admin.index') }}"
                                    class="dropdown-item gap-2 d-flex"
                                >
                                    <x-icon name="gear-fill" />
                                    <span>{{ trans('messages.admin-home') }}</span>
                                </a>
                            </li>
                        @endcan
                        <li>
                            <form action="{{ route('log_out') }}" method="POST">
                                @csrf
                                <button class="dropdown-item d-flex gap-2">
                                    <x-icon name="box-arrow-right" />
                                    <span>{{ trans('messages.log-out') }}</span>
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
            @endauth
        </ul>
    </div>
</nav>
