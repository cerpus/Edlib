{{-- TODO: report button, edlib branding, standalone link, etc etc. --}}
<x-layout no-nav no-header no-footer expand>
    <x-slot:title>{{ $version->title }}</x-slot:title>

    <x-lti-launch :launch="$launch" class="w-100 h-100" forwards-resize-messages />
</x-layout>
