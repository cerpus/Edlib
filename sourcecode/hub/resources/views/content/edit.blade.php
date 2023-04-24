<x-layout>
    <x-slot:title>Editing content {{ $content->latestPublishedVersion->resource->title }}</x-slot:title>

    <x-lti-launch :launch="$launch" />
</x-layout>
