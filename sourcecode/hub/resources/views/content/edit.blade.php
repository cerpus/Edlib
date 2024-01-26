<x-layout expand>
    <x-slot:title>Editing content {{ $content->latestPublishedVersion->title }}</x-slot:title>

    <x-lti-launch :launch="$launch" />

    @if (auth()->user()?->debug_mode ?? app()->hasDebugModeEnabled())
        <details class="container-md">
            <summary>Debug</summary>
            <x-lti-debug
                :url="$launch->getRequest()->getUrl()"
                :parameters="$launch->getRequest()->toArray()"
            />
        </details>
    @endif
</x-layout>
