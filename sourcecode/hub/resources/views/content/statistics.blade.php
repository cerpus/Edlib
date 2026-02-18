<x-layout no-header>
    <x-slot:title>{{trans('messages.statistics')}}</x-slot:title>

    <x-content.details.header :version="$content->latestVersion" current="statistics" />
    <div id="chart_usage"></div>

    <script nonce="{{\Illuminate\Support\Facades\Vite::cspNonce()}}">
        document.addEventListener('DOMContentLoaded', () => {
            window.usageChart('#chart_usage', @json($graph), '{{route('content.statistics', [$content])}}');
        });
    </script>
</x-layout>
