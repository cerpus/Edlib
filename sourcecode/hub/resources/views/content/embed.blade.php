{{-- TODO: report button, edlib branding, standalone link, etc etc. --}}
<x-layout no-nav no-header>
    <x-slot:title>{{ $version->title }}</x-slot:title>

    <x-lti-launch :launch="$launch" />
</x-layout>
