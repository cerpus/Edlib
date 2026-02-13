<x-layout no-header>
    <x-slot:title>{{ trans('messages.the-content-is-locked-for-editing') }}</x-slot:title>

    <p class="alert alert-danger">
        {{ trans('messages.the-content-is-locked-for-editing') }}
        @if ($lock)
            {{ trans('messages.the-lock-is-held-by', ['name' => $lock->user->name]) }}
        @endif
    </p>
</x-layout>
