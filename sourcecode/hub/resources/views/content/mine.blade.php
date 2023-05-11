<x-layout>
    <x-slot:title>{{ trans('messages.my-content') }}</x-slot:title>
    <x-content.search :query="$query" />
    <x-content.grid :contents="$contents" :show-drafts="true" />
</x-layout>
