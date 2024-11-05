<x-layout current="my-content">
    <x-slot:title>{{ trans('messages.my-content') }}</x-slot:title>

    <x-content.search :$contents :$filter mine />
</x-layout>
