@props(['current' => null])
<nav class="navbar navbar-expand">
    <div class="container-md d-flex justify-content-between">
        <a href="{{ route('home') }}" class="navbar-brand">
            <span
                aria-label="{{ config('app.name') }}"
                class="edlib-logo edlib-logo-navbar"
                role="img"
            ></span>
        </a>

        <ul class="nav nav-underline navbar-nav flex-grow-1 flex-nowrap justify-content-center">
            <li class="nav-item">
                <a
                    href="{{ route('content.index') }}"
                    @class(['nav-link', 'active' => $current === 'shared-content', 'd-flex', 'gap-2'])
                >
                    <x-icon name="globe-americas" />
                    <span class="d-none d-sm-block">{{ trans('messages.explore') }}</span>
                </a>
            </li>

            <li class="nav-item">
                <a
                    href="{{ route('content.create') }}"
                    @class(['nav-link', 'active' => $current === 'create', 'd-flex', 'gap-2'])
                >
                    <x-icon name="pencil" class="me-1" />
                    <span class="d-none d-sm-block">{{ trans('messages.create') }}</span>
                </a>
            </li>

            <li class="nav-item">
                <a
                    href="{{ route('content.mine') }}"
                    @class(['nav-link', 'active' => $current === 'my-content', 'd-flex', 'gap-2'])
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
                        @can('update-account')
                            <li>
                                <a
                                    href="{{ route('user.my-account') }}"
                                    class="dropdown-item gap-2 d-flex"
                                >
                                    <x-icon name="person-fill-gear"/>
                                    <span>{{ trans('messages.my-account') }}</span>
                                </a>
                            </li>
                        @endcan
                        <li>
                            <a
                                href="{{ route('user.preferences') }}"
                                class="dropdown-item gap-2 d-flex"
                            >
                                <x-icon name="wrench" />
                                <span>{{ trans('messages.preferences') }}</span>
                            </a>
                        </li>
                        @if (auth()->user()->debug_mode ?? false)
                            <li>
                                <a
                                    href="{{ route('lti.playground') }}"
                                    class="dropdown-item gap-2 d-flex"
                                >
                                    <x-icon name="dice-3-fill" />
                                    <span>{{ trans('messages.lti-playground') }}</span>
                                </a>
                            </li>
                        @endif
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
                        @can('logout')
                            <li>
                                <form action="{{ route('log_out') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item d-flex gap-2">
                                        <x-icon name="box-arrow-right" />
                                        <span>{{ trans('messages.log-out') }}</span>
                                    </button>
                                </form>
                            </li>
                        @endcan
                    </ul>
                </li>
            @else
                @can('login')
                    <li class="nav-item">
                        <a
                            href="{{ route('login') }}"
                            @class(['nav-link', 'active' => $current === 'login'])
                        >
                            {{ trans('messages.log-in') }}
                        </a>
                    </li>
                @endcan
            @endauth
        </ul>
    </div>
</nav>
