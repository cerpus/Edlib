<x-layout>
    <x-slot:title>{{ trans('messages.my-account') }}</x-slot:title>

    <h6>{{ trans('messages.change-profile-name') }}</h6>

    <x-form action="{{ route('user.save-name') }}">
        <x-form.field
            name="name"
            type="text"
            :label="trans('messages.name')"
            :value="old('name', $user->name)"
            required
        />

        <x-form.button class="btn-primary">
            {{ trans('messages.save') }}
        </x-form.button>
    </x-form>
</x-layout>
