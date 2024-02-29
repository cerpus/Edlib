<x-layout no-header>
    <x-slot:title>{{ config('app.name') }}</x-slot:title>

    <h2 class="fs-5 mb-3">{{ trans('messages.recent-content') }}</h2>

    <x-content.grid :contents="$contents" title-previews />
</x-layout>
