{{-- TODO: report button, edlib branding, standalone link, etc etc. --}}
<x-layout :nav="false" :show-header="false">
    <x-slot:title>{{ $version->title }}</x-slot:title>

    <x-lti-launch :launch="$launch" />
</x-layout>