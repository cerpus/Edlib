<x-layout>
    <x-slot:title>Preview</x-slot:title>

    <x-lti-launch
        :launchUrl="$content->latestVersion->resource->view_launch_url"
        :ltiVersion="$content->latestVersion->resource->tool->lti_version"
        :preview="true"
    />
</x-layout>
