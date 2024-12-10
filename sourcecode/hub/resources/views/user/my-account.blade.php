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
            autocomplete="new-password"
        />

        <x-form.field
            name="password_confirmation"
            type="password"
            :label="trans('messages.password-confirmation')"
            autocomplete="new-password"
        />

        <h5>{{ trans('messages.change-email') }}</h5>

        <x-form.field
            name="email"
            type="email"
            :label="trans('messages.email-address')"
            :value="old('email', $user->email)"
        />

        <x-form.button class="btn-primary">
            {{ trans('messages.save') }}
        </x-form.button>
    </x-form>

    @if ($user->google_id || $user->facebook_id)
        <hr>
        <x-form action="{{ route('user.disconnect-social-accounts') }}">
            <h5>{{ trans('messages.disconnect-social-accounts') }}</h5>

            @if ($user->google_id && !empty($user->password))
                <x-form.button class="btn-primary" name="disconnect-google">
                    {{ trans('messages.disconnect-google') }}
                </x-form.button>
            @endif

            @if ($user->facebook_id && !empty($user->password))
                <x-form.button class="btn-primary" name="disconnect-facebook">
                    {{ trans('messages.disconnect-facebook') }}
                </x-form.button>
            @endif

            @if (empty($user->password))
                <p>{{ trans('messages.alert-password-empty') }}</p>
            @endif
        </x-form>
    @endif

    {{-- TODO: make less unpleasant --}}
    @if ($user->debug_mode)
        <details class="mt-3" lang="en">
            <summary>API credentials</summary>
            <pre>URL: <a href="{{ url('/api') }}">{{ url('/api') }}</a><br>Key: <kbd class="user-select-all">{{ $user->getApiKey() }}</kbd><br>Secret: <kbd class="user-select-all">{{ $user->getApiSecret() }}</pre>
            <pre>Header: <kbd class="user-select-all">{{ $user->getApiAuthorization() }}</kbd></pre>
            <pre>curl: <kbd>curl --basic --user {{ $user->getApiKey() }}:{{ $user->getApiSecret() }} {{ url('/api') }}</kbd></pre>
        </details>
    @endif
</x-layout>
