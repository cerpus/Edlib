@props(['version', 'explicitVersion' => false, 'current' => null])
@php($content = $version->content)

@if (!$version->published)
    <p class="alert alert-warning" role="alert">
        {{ trans('messages.viewing-draft-version-notice') }}
        @if ($explicitVersion && $content->latestPublishedVersion()->exists())
            <a href="{{ route('content.details', [$content]) }}">{{ trans('messages.view-latest-published-version') }}</a>
        @endif
    </p>
@elseif ($explicitVersion && !$content->latestPublishedVersion()->is($version))
    <p class="alert alert-info">
        {{ trans('messages.viewing-old-version-notice') }}
        @if ($content->latestPublishedVersion()->exists())
            <a href="{{ route('content.details', $content) }}">{{ trans('messages.view-latest-version') }}</a>
        @endif
    </p>
@endif

<div class="d-flex gap-3 align-items-center">
    @if ($version->icon)
        <img
            src="{{ $version->icon->getUrl() }}"
            alt=""
            class="content-icon content-icon-128"
            aria-hidden="true"
        >
    @endif

    <div class="flex-grow-1">
        <h1 class="fs-2">{{ $version->title }}</h1>

        {{-- TODO: Show more author names if there are any --}}
        <p>{{ trans('messages.created')}}:
            <time datetime={{$version->created_at->toIso8601String()}} data-dh-relative="true"></time>
            {{ trans('messages.by')}} {{ $content->users()->first()?->name }}
        </p>
    </div>
</div>

@can('edit', [$content])
    <nav class="mb-2">
        <ul class="nav nav-underline">
            <li class="nav-item">
                <a
                    href="{{ route('content.details', [$content]) }}"
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
        </ul>
    </nav>
@endcan
