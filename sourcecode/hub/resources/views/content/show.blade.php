<x-layout>
    <x-slot:title>{{ $content->latestPublishedVersion->resource->title }}</x-slot:title>

    <x-lti-launch :launch="$launch" />
</x-layout>
