<x-layout>
    <x-slot:title>{{ trans('messages.explore') }}</x-slot:title>

    <livewire:search wire:model.live="query" />
</x-layout>
