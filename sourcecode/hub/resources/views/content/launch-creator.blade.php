<x-layout expand no-header no-footer>
    <x-slot:title>{{ trans('messages.creating-content-with-tool', ['tool' => $tool->name]) }}</x-slot:title>

    <x-lti-launch :launch="$launch" class="w-100 h-100" />

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
