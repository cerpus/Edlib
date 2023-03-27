<x-layout>
    <x-slot:title>Editing content {{ $content->latestVersion->resource->title }}</x-slot:title>

    <x-lti-launch
        :launchUrl="$content->latestVersion->resource->edit_launch_url"
        :ltiTool="$content->latestVersion->resource->tool"
    />
</x-layout>
