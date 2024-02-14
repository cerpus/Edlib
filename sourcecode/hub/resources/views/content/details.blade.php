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

                    <p><a href="{{ route('content.share', [$content]) }}" class="text-body-emphasis">{{ route('content.share', [$content]) }}</a></p>
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
                <section>
                    <h2 class="fs-5">{{ trans('messages.version-history') }}</h2>

                    <ul class="list-unstyled d-flex flex-column gap-2 version-history">
                        @foreach ($content->versions as $v)
                            @php
                                $isLatest = $content->latestVersion->is($v);
                                $isCurrent = $version->is($v);
                            @endphp

                            <li
                                @class([
                                    'position-relative',
                                    'text-body',
                                    'p-3',
                                    'border',
                                    'rounded',
                                    'd-flex',
                                    'border-success-subtle' => $v->published,
                                    'published' => $v->published,
                                    'draft' => !$v->published,
                                    'bg-success-subtle' => $content->latestPublishedVersion?->is($v),
                                ])
                            >
                        <span class="flex-grow-1">
                            <a
                                href="{{ route('content.version-details', [$content, $v]) }}"
                                @class([
                                    'd-block',
                                    'text-body',
                                    'link-underline-opacity-0',
                                    'link-underline-opacity-100-hover',
                                    'stretched-link',
                                    'link-underline-success' => $v->published,
                                    'link-underline' => !$v->published,
                                    'fw-bold' => $isCurrent,
                                ])
                            >
                                <time datetime="{{ $v->created_at->format('c') }}">{{ $v->created_at }}</time>
                            </a>

                            @if ($content->latestPublishedVersion?->is($v))
                                <small class="d-block">{{ trans('messages.current-published-version') }}</small>
                            @elseif ($content->latestDraftVersion?->is($v))
                                <small class="d-block">{{ trans('messages.latest-draft') }}</small>
                            @endif
                        </span>

                                @if ($v->published)
                                    <x-icon name="check-circle" label="{{ trans('messages.published') }}" class="text-success" />
                                @else
                                    <x-icon name="pencil" label="{{ trans('messages.draft') }}" class="text-body-secondary" />
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if (auth()->user()?->debug_mode ?? app()->hasDebugModeEnabled())
                @php($version = $content->latestVersion)

                <details>
                    <summary class="fs-5">LTI params</summary>

                    <dl>
                        <dt>ID</dt>
                        <dd><kbd>{{ $content->id }}</kbd></dd>

                        <dt>Version ID</Dt>
                        <dd><kbd>{{ $version->id }}</kbd></dd>

                        <dt>Tool ID</dt>
                        <dd><kbd>{{ $version->lti_tool_id }}</kbd></dd>

                        <dt>Presentation launch URL</dt>
                        <dd><kbd>{{ $version->lti_launch_url }}</kbd></dd>
                    </dl>

                    <x-lti-debug
                        :url="$launch->getRequest()->getUrl()"
                        :parameters="$launch->getRequest()->toArray()"
                    />
                </details>

                <details>
                    <summary class="fs-5">Messages</summary>

                    <pre
                        id="messages"
                        class="debug-messages border font-monospace overflow-scroll"
                        aria-label="messages"
                        readonly
                    ></pre>
                </details>
            @endif
        </x-slot:sidebar>
    </x-layout.sidebar-layout>
</x-layout>
