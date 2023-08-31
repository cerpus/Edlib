<x-layout>
    <x-slot:title>{{ trans('messages.my-content') }}</x-slot:title>

    @unless ($contents->isEmpty())
        <livewire:my-content-search :user="auth()->user()" wire:model="query"/>
    @else
        <div class="no-content-found d-flex flex-column justify-content-center align-items-center">
            <h1 class="no-content-found-title d-flex">{{ trans('messages.alert-no-content-found-header') }}</h1>
            <p class="d-flex">{{ trans('messages.alert-no-content-found-description') }}</p>

            <div class="d-flex gap-3 flex-column flex-md-row">
                <a href="{{ route('content.index') }}" class="btn btn-primary" > {{ trans('messages.find-content') }} </a>
                <a href="{{ route('content.create') }}" class="btn btn-secondary"> {{ trans('messages.create-content') }} </a>
            </div>
        </div>
    @endunless
</x-layout>
