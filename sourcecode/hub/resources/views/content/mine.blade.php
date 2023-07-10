<x-layout>
    <x-slot:title>{{ trans('messages.my-content') }}</x-slot:title>

    <livewire:my-content-search :user="auth()->user()"/>
</x-layout>
