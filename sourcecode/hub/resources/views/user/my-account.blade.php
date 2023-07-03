<x-layout>
    <x-slot:title>{{ trans('messages.my-account') }}</x-slot:title>

    <x-form action="{{ route('user.update-account') }}">
        <h5>{{ trans('messages.change-profile-name') }}</h5>

        <x-form.field
            name="name"
            type="text"
            :label="trans('messages.name')"
            :value="old('name', $user->name)"
            required
        />

        <h5>{{ trans('messages.change-password') }}</h5>

        <x-form.field
            name="password"
            type="password"
            :label="trans('messages.password')"
        />

        <x-form.field
            name="password_confirmation"
            type="password"
            :label="trans('messages.password-confirmation')"
        />

        <x-form.button class="btn-primary">
            {{ trans('messages.save') }}
        </x-form.button>
    </x-form>
</x-layout>
