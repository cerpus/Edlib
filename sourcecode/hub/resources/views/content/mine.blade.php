<x-layout>
    <x-slot:title>{{ trans('messages.my-content') }}</x-slot:title>

    @unless ($contents->isEmpty())
        <livewire:my-content-search :user="auth()->user()" wire:model="query"/>
    @else
        <div class="no-content-found">
            <h1 class="no-content-found-title">{{ trans('messages.alert-no-content-found-header') }}</h1>
            <p class="no-content-found-description">{{ trans('messages.alert-no-content-found-description') }}</p>

            <div class="d-flex justify-content-center mt-3 flex-column flex-md-row">
                <a
                    href="{{ route('content.index') }}"
                    class="btn btn-primary mb-2 mb-md-0 me-md-2"
                >
                    {{ trans('messages.find-content') }}
                </a>

                <a
                    href="{{ route('content.create') }}"
                    class="btn btn-custom"
                >
                    {{ trans('messages.create-content') }}
                </a>
            </div>
        </div>
    @endunless
</x-layout>
