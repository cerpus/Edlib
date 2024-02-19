@php use App\Support\SessionScope; @endphp
@props([
    'version' => $version ?? $content->latestPublishedVersion,
    'pinnedVersion' => isset($version),
])
<x-layout no-header>
    <x-slot:title>{{ $version->title }}</x-slot:title>

    <x-slot:head>
        <x-oembed-links />
    </x-slot:head>

    <x-layout.sidebar-layout>
        <x-slot:main>
            @if (!$version->published)
                <p class="alert alert-warning" role="alert">
                    {{ trans('messages.viewing-draft-version-notice') }}
                    @if ($pinnedVersion && $content->latestPublishedVersion)
                        <a href="{{ route('content.details', [$content]) }}">{{ trans('messages.view-latest-version') }}</a>
                    @endif
                </p>
            @elseif ($pinnedVersion && !$content->latestPublishedVersion?->is($version))
                <p class="alert alert-info">
                    {{ trans('messages.viewing-old-version-notice') }}
                    @if ($content->latestPublishedVersion)
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
                    <p>{{ trans('messages.created')}}: {{ $version->created_at->isoFormat('LL') }} {{ trans('messages.by')}} {{ $content->users()->first()?->name }}</p>

                    <p>
                        <a
                            href="{{ route('content.share', [$content, SessionScope::TOKEN_PARAM => null]) }}"
                            class="text-body-emphasis"
                        >
                            {{ route('content.share', [$content, SessionScope::TOKEN_PARAM => null]) }}
                        </a>
                    </p>
                </div>
            </div>

            <x-lti-launch :launch="$launch" log-to="#messages" class="w-100 border mb-2" />

            <div class="d-flex flex-gap gap-2">
                {{-- TODO: be able to edit pinned versions --}}
                @if (!$pinnedVersion)
                    @can('edit', $content)
                        <a href="{{ route('content.edit', [$content]) }}" class="btn btn-secondary">
                            <x-icon name="pencil" class="me-1" />
                            {{ trans('messages.edit')}}
                        </a>
                    @endcan
                @endif

                {{-- TODO: be able to use pinned versions --}}
                @if (!$pinnedVersion)
                    @can('use', $content)
                        <x-form action="{{ route('content.use', [$content]) }}">
                            <button class="btn btn-secondary">
                                {{ trans('messages.use-content')}}
                            </button>
                        </x-form>
                    @endcan
                @endif
            </div>
        </x-slot:main>

        <x-slot:sidebar>
            @can('edit', $content)
                <x-content.details.version-history :$version />
            @endif

            @if (auth()->user()?->debug_mode ?? app()->hasDebugModeEnabled())
                <x-content.details.lti-params :$launch :$version />
                <x-content.details.messages id="messages" />
            @endif
        </x-slot:sidebar>
    </x-layout.sidebar-layout>
</x-layout>
