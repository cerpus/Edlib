<x-layout current="shared-content">
    <x-slot:title>{{ trans('messages.explore') }}</x-slot:title>

    <x-content.search :$contents :$filter />
</x-layout>
