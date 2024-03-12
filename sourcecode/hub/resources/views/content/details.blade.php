@php use App\Support\SessionScope; @endphp
@props(['version', 'explicitVersion' => false])
<x-layout no-header>
    <x-slot:title>{{ $version->title }}</x-slot:title>

    <x-slot:head>
        <x-oembed-links />
    </x-slot:head>

    <x-layout.sidebar-layout>
        <x-slot:top>
            @if (!$version->published)
                <p class="alert alert-warning" role="alert">
                    {{ trans('messages.viewing-draft-version-notice') }}
                    @if ($explicitVersion && $content->latestPublishedVersion()->exists())
                        <a href="{{ route('content.details', [$content]) }}">{{ trans('messages.view-latest-version') }}</a>
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
        </x-slot:top>

        <x-slot:main>
            <x-lti-launch :launch="$launch" log-to="#messages" class="w-100 border mb-2" />
        </x-slot:main>

        <x-slot:sidebar>
            <div class="d-flex flex-lg-column gap-2 mb-5 details-action-buttons">
                @can('use', $content)
                    <x-form action="{{ route('content.use', [$content]) }}">
                        <button class="btn btn-primary btn-lg d-flex gap-2 w-100">
                            <x-icon name="check-lg" />
                            <span class="flex-grow-1">{{ trans('messages.use-content')}}</span>
                        </button>
                    </x-form>
                @endcan

                @can('edit', $content)
                    <a href="{{ route('content.edit', [$content, $version]) }}" class="btn btn-secondary btn-lg d-flex gap-2">
                        <x-icon name="pencil" class="align-self-start" />
                        <span class="flex-grow-1 align-self-center">{{ trans('messages.edit')}}</span>
                    </a>
                @endcan

                @if (!$explicitVersion && $version->published)
                    <a
                        href="{{ route('content.share', [$content, SessionScope::TOKEN_PARAM => null]) }}"
                        class="btn btn-secondary d-flex gap-2 btn-lg share-button"
                        target="_blank"
                        data-share-success-message="{{ trans('messages.share-copied-url-success') }}"
                        data-share-failure-message="{{ trans('messages.share-copied-url-failed') }}"
                    >
                        <x-icon name="share" />
                        <span class="flex-grow-1 align-self-center">{{ trans('messages.share') }}</span>
                    </a>
                @endif

                @can('delete', $content)
                    <button
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteModal"
                        class="btn btn-outline-danger btn-lg d-flex gap-2 w-100 delete-content-button"
                    >
                        <x-icon name="x-lg" class="align-self-start" />
                        <span class="flex-grow-1 align-self-center">{{ trans('messages.delete-content') }}</span>
                    </button>

                    <div id="deleteModal" class="modal modal-blur fade" style="display: none" aria-hidden="true" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-fullscreen-lg-down modal-lg">
                            <div class="modal-content">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title">{{ trans('messages.delete-content') }}</h5>
                                </div>
                                <div class="modal-body">
                                    <p>
                                        {{ trans('messages.delete-content-confirm-text') }}
                                    </p>
                                </div>
                                <div class="modal-footer border-0">
                                    <button
                                        type="button"
                                        class="btn btn-primary"
                                        hx-delete="{{ route('content.delete', [$content]) }}"
                                        hx-disabled-elt="button"
                                    >
                                        <span class="flex-grow-1 align-self-center">{{ trans('messages.delete-content') }}</span>
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        {{ trans('messages.cancel') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
            </div>

            @can('edit', $content)
                <x-content.details.version-history :$version />
            @endcan

            @if (auth()->user()?->debug_mode ?? app()->hasDebugModeEnabled())
                <x-content.details.lti-params :$launch :$version />
                <x-content.details.messages id="messages" />
            @endif
        </x-slot:sidebar>
    </x-layout.sidebar-layout>
</x-layout>
