@props([
    'version' => $version ?? $content->latestPublishedVersion,
    'pinnedVersion' => isset($version),
])
<x-layout>
    <x-slot:title>{{ $version->resource->title }}</x-slot:title>

    <x-slot:head>
        <x-oembed-links />
    </x-slot:head>

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

    {{-- TODO: Show more author names if there are any --}}
    <p>{{ trans('messages.created')}}: {{ $content->created_at->isoFormat('LL') }} {{ trans('messages.by')}} {{ $content->users()->first()->name }}</p>

    <p>{{ trans('messages.last-updated')}}: {{ $content->updated_at->isoFormat('LL') }}</p>

    <x-lti-launch :launch="$launch" />

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

    {{-- TODO: --}}
{{--    @can('delete', $content)--}}
{{--        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#deletionModal">--}}
{{--            <x-icon name="trash" class="me-1" />--}}
{{--            {{ trans('messages.delete')}}--}}
{{--        </button>--}}
{{--    @endcan--}}
{{--    </div>--}}

{{--    <hr>--}}
{{--    <div class="justify-content-around">--}}
{{--        <ul class="nav nav-underline d-flex w-100 justify-content-between">--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link active" aria-current="page" href="#">{{ trans('messages.licensing')}}</a>--}}
{{--            </li>--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link" href="#">{{ trans('messages.user-statistics')}} <x-icon name="graph-up-arrow" class="text-black"/></a>--}}
{{--            </li>--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link" href="#">{{ trans('messages.using-resource-on-lms')}}</a>--}}
{{--            </li>--}}
{{--        </ul>--}}
{{--    </div>--}}

    <x-slot:sidebar>
        <section>
            <h2 class="fs-5">{{ trans('messages.version-history') }}</h2>

            <ul class="list-unstyled d-flex flex-column gap-2">
                @foreach ($content->versions as $v)
                    @php($isLatest = $content->latestVersion->is($v))
                    @php($isCurrent = $version->is($v))
                    <li>
                        {{-- TODO: hover styles --}}
                        <a
                            href="{{ route('content.version-details', [$content, $v]) }}"
                            class="text-body text-decoration-none p-3 border rounded d-flex
                                   @if($v->published) border-success @endif
                                   @if($content->latestPublishedVersion?->is($v)) bg-success-subtle @endif
                                  "
                        >
                            <span class="flex-grow-1">
                                <span class="d-block @if ($isCurrent) fw-bold @endif">
                                    <time datetime="{{ $v->created_at->format('c') }}">{{ $v->created_at }}</time>
                                </span>

                                @if ($content->latestPublishedVersion?->is($v))
                                    <small class="d-block">{{ trans('messages.current-published-version') }}</small>
                                @elseif ($isLatest)
                                    <small class="d-block">{{ trans('messages.latest-unpublished-draft') }}</small>
                                @endif
                            </span>

                            <span>
                                @if ($v->published)
                                    <x-icon name="check-circle" label="{{ trans('messages.published') }}" class="text-success" />
                                @else
                                    <x-icon name="pencil" label="{{ trans('messages.draft') }}" class="text-body-secondary" />
                                @endif
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>

        @if (auth()->user()?->debug_mode ?? app()->hasDebugModeEnabled())
            @php($version = $content->latestVersion)

            <details>
                <summary class="fs-5">Debug</summary>

                <dl>
                    <dt>ID</dt>
                    <dd><kbd>{{ $content->id }}</kbd></dd>

                    <dt>Version ID</Dt>
                    <dd><kbd>{{ $version->id }}</kbd></dd>

                    <dt>Resource ID</dt>
                    <dd><kbd>{{ $version->lti_resource_id }}</kbd></dd>

                    <dt>Presentation launch URL</dt>
                    <dd><kbd>{{ $version->resource->view_launch_url }}</kbd></dd>
                </dl>

                <x-lti-debug :request="$launch->getRequest()" />
            </details>
        @endif
    </x-slot:sidebar>

{{--    <x-delete-modal />--}}
</x-layout>
