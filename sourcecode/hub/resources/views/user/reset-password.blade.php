<x-layout>
    <x-slot:title>{{ trans('messages.reset-password') }}</x-slot:title>

    <x-form action="{{ route('reset-password-update', [$user->password_reset_token]) }}">
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
