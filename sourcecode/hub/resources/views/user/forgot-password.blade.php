<x-layout>
    <x-slot:title>{{ trans('messages.reset-password') }}</x-slot:title>

    <x-form action="{{ route('forgot-password-send') }}">
        <x-form.field
            name="email"
            type="email"
            :label="trans('messages.email-address')"
            required
        />

        <x-form.button class="btn-primary">
            {{ trans('messages.submit') }}
        </x-form.button>
    </x-form>
</x-layout>
