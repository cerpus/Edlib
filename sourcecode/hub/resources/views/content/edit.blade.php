<x-layout expand no-header no-footer>
    <x-slot:title>{{ trans('messages.editing-content-title', ['title' => $version->getTitle()]) }}</x-slot:title>

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

    {{-- TODO: error message if lock failed to refresh --}}
    <div
        hx-put="{{ route('content.refresh-lock', [$content]) }}"
        hx-trigger="every {{ \App\Models\ContentLock::REFRESH_TIME_SECONDS }}s"
        hidden
    ></div>

    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
        addEventListener('unload', function () {
            const data = new FormData();
            data.set('_token', {!! json_encode(csrf_token()) !!});

            navigator.sendBeacon({!! json_encode(route('content.release-lock', [$content])) !!}, data);
        });
    </script>
</x-layout>
