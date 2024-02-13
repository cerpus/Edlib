<x-layout no-header>
    <x-slot:title>{{ trans('messages.log-in') }}</x-slot:title>

    <div class="row gx-0 gy-5">
        @if ($has_google || $has_facebook || $has_auth0)
            <div class="d-flex flex-column gap-3 col-lg-5">
                <h2 class="fs-5 mb-3">{{ trans('messages.log-in-using') }}</h2>

                @if ($has_google)
                    <a
                        href="{{ route('social.login', ['google']) }}"
                        class="btn btn-secondary d-flex gap-2 justify-content-center"
                        aria-label="{{ trans('messages.log-in-with-google') }}"
                    >
                        <x-icon name="google" />
                        {{ trans('messages.log-in-google') }}
                    </a>
                @endif

                @if ($has_facebook)
                    <a
                        href="{{ route('social.login', ['facebook']) }}"
                        class="btn btn-secondary d-flex gap-2 justify-content-center"
                        aria-label="{{ trans('messages.log-in-with-facebook') }}"
                    >
                        <x-icon name="facebook" />
                        {{ trans('messages.log-in-facebook') }}
                    </a>
                @endif

                @if ($has_auth0)
                    <a
                        href="{{ route('social.login', ['auth0']) }}"
                        class="btn btn-secondary d-flex gap-2 justify-content-center"
                        aria-label="{{ trans('messages.log-in-with-auth0') }}"
                    >
                        <x-icon name="door-open-fill" /> {{-- TODO: real auth0 icon --}}
                        {{ trans('messages.log-in-auth0') }}
                    </a>
                @endif
            </div>
        @endif

        @if ($has_google || $has_facebook || $has_auth0)
            <div class="col-lg-2 d-flex d-column justify-content-center align-items-center" aria-hidden="true">
                {{ trans('messages.or') }}
            </div>
        @endif

        <x-form action="{{ route('login_check') }}" class="col-lg-5">
            <h2 class="fs-5 mb-3">{{ trans('messages.log-in-with-email-and-password') }}</h2>

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

            <div class="d-flex flex-wrap gap-2 mb-3">
                <x-form.button class="btn-primary">
                    {{ trans('messages.log-in') }}
                </x-form.button>

                @can('reset-password')
                    <a href="{{ route('forgot-password') }}" class="btn btn-secondary">
                        {{ trans('messages.forgot-password') }}
                    </a>
                @endcan
            </div>

            @can('register')
                <p>
                    {{ trans('messages.no-account-yet') }}
                    <a href="{{ route('register') }}">{{ trans('messages.sign-up') }}</a>
                </p>
            @endcan
        </x-form>
    </div>
</x-layout>
