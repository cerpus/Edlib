<x-layout>
    <x-slot:title>{{ trans('messages.registration') }}</x-slot:title>

    <x-form>
        <x-form.field
            name="name"
            type="text"
            :label="trans('messages.name')"
            :text="trans('messages.name-form-help')"
            required
        />

        <x-form.field
            name="email"
            type="email"
            :label="trans('messages.email-address')"
            required
        />

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
            {{ trans('messages.sign-up') }}
        </x-form.button>
    </x-form>
</x-layout>
