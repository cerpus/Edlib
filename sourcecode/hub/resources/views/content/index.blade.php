<x-layout>
    <x-slot:title>{{ trans('messages.shared-content') }}</x-slot:title>
    <x-content.search :query="$query" />
    <x-content.grid :contents="$contents" />
</x-layout>