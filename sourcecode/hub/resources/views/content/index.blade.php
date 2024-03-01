<x-layout>
    <x-slot:title>{{ trans('messages.explore') }}</x-slot:title>

    <x-content.search :$contents :$filter />
</x-layout>
