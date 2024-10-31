@props(['version', 'explicitVersion' => false, 'current' => null])
@php($content = $version->content)

<div class="row gap-3 align-items-center">
    @if ($version->icon)
        <img
            src="{{ $version->icon->getUrl() }}"
            alt=""
            class="col-auto content-icon content-icon-128"
            aria-hidden="true"
        >
    @endif

    <div class="col d-flex flex-column">
        <h1 class="fs-2">{{ $version->title }}</h1>
    </div>
    <div class="col-3 d-none d-lg-flex align-self-start justify-content-end flex-wrap gap-2">
        <x-content.details.action-buttons :$content :$version :$explicitVersion />
    </div>
</div>

@can('edit', [$content])
    <nav class="mb-2">
        <ul class="nav nav-underline">
            <li class="nav-item">
                <a
                    href="{{ $content->getDetailsUrl() }}"
                    @class(['nav-link', 'active' => $current === 'content'])
                    @if ($current === 'content') aria-current="page" @endif
                >
                    {{ trans('messages.content') }}
                </a>
            </li>
            <li class="nav-item">
                <a
                    href="{{ route('content.history', [$content]) }}"
                    @class(['nav-link', 'active' => $current === 'history'])
                    @if ($current === 'history') aria-current="page" @endif
                >
                    {{ trans('messages.history') }}
                </a>
            </li>
            <li class="nav-item">
                <a
                    href="{{ route('content.roles', [$content]) }}"
                    @class(['nav-link', 'active' => $current === 'roles'])
                    @if ($current === 'roles') aria-current="page" @endif
                >
                    {{ trans('messages.roles') }}
                </a>
            </li>
            <li class="nav-item">
                <a
                    href="{{ route('content.statistics', [$content]) }}"
                    @class(['nav-link', 'active' => $current === 'statistics'])
                    @if ($current === 'statistics') aria-current="page" @endif
                >
                    {{ trans('messages.statistics') }}
                </a>
            </li>
        </ul>
    </nav>
@endcan
