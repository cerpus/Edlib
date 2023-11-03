<x-sidebar-layout>
    <x-slot:title>{{ $content->latestPublishedVersion->resource->title }}</x-slot:title>

    <x-slot:head>
        <x-oembed-links />
    </x-slot:head>

    <x-lti-launch :launch="$launch" />

    <x-slot:sidebar>
        @can('edit', $content)
            <section>
                <h2 class="fs-5">{{ trans('messages.version-history') }}</h2>

                <ul class="p-0">
                    @foreach ($content->versions as $version)
                        <li class="d-block p-1 mb-1 {{ $version->published ? ($content?->latestPublishedVersion->is($version) ? 'bg-success text-bg-success' : 'bg-success-subtle'): 'bg-danger-subtle' }}">
                            {{ $version->created_at }}
                            @if ($content?->latestPublishedVersion->is($version))
                                <span class="d-block">{{ trans('messages.published') }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endcan

        @if (auth()->user()?->debug_mode ?? app()->hasDebugModeEnabled())
            @php($version = $content->latestVersion)

            <details>
                <summary>Debug</summary>

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
</x-sidebar-layout>
