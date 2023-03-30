<x-layout>
    <x-slot:title>{{ $content->latestVersion->resource->title }}</x-slot:title>

    <x-lti-launch :launch="$launch" />
</x-layout>
