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

            @can('reset-password')
                <a href="{{ route('forgot-password') }}" class="btn btn-secondary">
                    {{ trans('messages.forgot-password') }}
                </a>
            @endcan
        </div>
    </x-form>

    <hr>

    <div class="d-flex flex-wrap gap-2">
        @if ($has_google)
            <a href="{{ route('social.login', ['google']) }}" class="btn btn-secondary d-flex gap-2">
                <x-icon name="google" />
                {{ trans('messages.log-in-google') }}
            </a>
        @endif

        @if ($has_facebook)
            <a href="{{ route('social.login', ['facebook']) }}" class="btn btn-secondary d-flex gap-2">
                <x-icon name="facebook" />
                {{ trans('messages.log-in-facebook') }}
            </a>
        @endif

        @if ($has_auth0)
            <a href="{{ route('social.login', ['auth0']) }}" class="btn btn-secondary d-flex gap-2">
                <x-icon name="door-open-fill" /> {{-- TODO: real auth0 icon --}}
                {{ trans('messages.log-in-auth0') }}
            </a>
        @endif
    </div>
</x-layout>
