<x-sidebar-layout>
    <x-slot:title>{{ sprintf('Create a thing with %s', $tool->name) }}</x-slot:title>

    <x-lti-launch :launch="$launch" />

    <x-slot:sidebar>
        @if (auth()->user()?->debug_mode ?? app()->hasDebugModeEnabled())
            <details>
                <summary>Debug</summary>
                <x-lti-debug :request="$launch->getRequest()" />
            </details>
        @endif
    </x-slot:sidebar>
</x-sidebar-layout>
