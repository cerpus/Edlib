<x-layout>
    <x-slot:title>{{ trans('messages.preferences') }}</x-slot:title>

    <x-form action="{{ route('user.save-preferences') }}">
        {{-- TODO: errors on each form field --}}
        @if ($errors->any())
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <div class="mb-3 row">
            <label class="form-label col-12 col-md-2 col-form-label" for="locale">
                {{ trans('messages.language') }}
            </label>

            <div class="col-12 col-md-6 col-lg-4">
                <x-form.dropdown
                    name="locale"
                    id="locale"
                    :options="$locales"
                    :selected="$user->locale"
                    :disabled="!$canChangeLocale"
                />
            </div>
        </div>

        <div class="mb-3 row">
            <label class="form-label col-12 col-md-2 col-form-label" for="theme">
                {{ trans('messages.theme') }}
            </label>

            <div class="col-12 col-md-6 col-lg-4">
                <x-form.dropdown
                    name="theme"
                    id="theme"
                    :options="$themes"
                    :selected="$user->theme"
                    :emptyOption="trans('messages.default')"
                />
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12 col-md-10 offset-md-2">
                <div class="form-check">
                    <x-form.checkbox
                        name="debug_mode"
                        :checked="old('debug_mode', $user->debug_mode)"
                        aria-labelledby="debug_mode_form_help"
                    />
                    <label for="debug_mode">{{ trans('messages.debug-mode') }}</label>
                    <div class="form-text" id="debug_mode_form_help">
                        {{ trans('messages.debug-mode-form-help') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <x-form.button class="btn-primary">{{ trans('messages.save') }}</x-form.button>
        </div>
    </x-form>
</x-layout>
