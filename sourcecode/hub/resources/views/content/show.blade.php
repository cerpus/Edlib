<x-sidebar-layout>
    <x-slot:title>{{ $content->latestPublishedVersion->resource->title }}</x-slot:title>

    <x-slot:head>
        <x-oembed-links />
    </x-slot:head>

    <x-lti-launch :launch="$launch" />

    <x-slot:sidebar>
        @if (auth()->user()?->debug_mode ?? false)
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

                    <dt>LTI launch URL</dt>
                    <dd><kbd>{{ $launch->getRequest()->getUrl() }}</kbd></dd>

                    <dt>LTI parameters</dt>
                    <dd>
                        <dl>
                            @foreach ($launch->getRequest()->toArray() as $key => $parameter)
                                <dt>{{ $key }}</dt>
                                <dd><kbd>{{ $parameter }}</kbd></dd>
                            @endforeach
                        </dl>
                    </dd>
                </dl>
            </details>
        @endif
    </x-slot:sidebar>
</x-sidebar-layout>
