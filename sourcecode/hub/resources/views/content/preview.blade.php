<x-layout>
    <x-slot:title>{{ $content->latestVersion->resource->title }}</x-slot:title>

    <x-lti-launch
        :ltiTool="$content->latestVersion->resource->tool"
        :launchUrl="$content->latestVersion->resource->view_launch_url"
        :preview="true"
    />
</x-layout>
