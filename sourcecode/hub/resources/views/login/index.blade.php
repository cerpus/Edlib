<x-layout>
    <x-slot:title>{{ trans('messages.log-in') }}</x-slot:title>

    <x-form action="{{ route('login_check') }}">
        <x-form.field
            name="email"
            :label="trans('messages.email-address')"
            required
        />

        <x-form.field
            name="password"
            :label="trans('messages.password')"
            type="password"
            required
        />

        <x-form.button class="btn-primary">
            {{ trans('messages.log-in') }}
        </x-form.button>
    </x-form>
    <hr>
    <a href="{{ route('google.login') }}" class="btn btn-primary btn-user btn-block">
        {{ trans('messages.log-in-google') }}
    </a>
    <a href="{{ route('facebook.login') }}" class="btn btn-primary btn-user btn-block">
        {{ trans('messages.log-in-facebook') }}
    </a>
</x-layout>
