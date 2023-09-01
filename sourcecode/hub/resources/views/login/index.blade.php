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

        <div class="d-grid d-md-block gap-2">
            <x-form.button class="btn-primary">
                {{ trans('messages.log-in') }}
            </x-form.button>

            <a href="{{ route('forgot-password') }}" class="btn btn-secondary">
                {{ trans('messages.forgot-password') }}
            </a>
        </div>
    </x-form>

    <hr>

    <div class="d-grid d-md-block gap-2">
        <a href="{{ route('google.login') }}" class="btn btn-primary">
            {{ trans('messages.log-in-google') }}
        </a>
        <a href="{{ route('facebook.login') }}" class="btn btn-primary">
            {{ trans('messages.log-in-facebook') }}
        </a>
    </div>
</x-layout>
