<x-layout>
    <x-slot:title>{{ trans('messages.reset-password') }}</x-slot:title>

    <x-form action="{{ route('reset-password-update', ['token' => $token]) }}">
        <input type="hidden" name="token" value="{{ $token }}">

        <x-form.field
            name="password"
            type="password"
            :label="trans('messages.password')"
            required
        />

        <x-form.field
            name="password_confirmation"
            type="password"
            :label="trans('messages.password-confirmation')"
            required
        />

        <x-form.button class="btn-primary">
            {{ trans('messages.submit') }}
        </x-form.button>
    </x-form>
</x-layout>
