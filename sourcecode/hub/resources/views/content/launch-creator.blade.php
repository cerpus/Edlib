<x-layout>
    <x-slot:title>{{ sprintf('Create a thing with %s', $tool->name) }}</x-slot:title>

    <x-lti-launch :launch="$launch" />
</x-layout>
