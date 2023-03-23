<x-layout>
    <x-slot:title>{{ sprintf('Create a thing with %s', $tool->name) }}</x-slot:title>

    <x-lti-launch
        :launchUrl="$tool->creator_launch_url"
        :ltiVersion="$tool->lti_version"
    />
</x-layout>
