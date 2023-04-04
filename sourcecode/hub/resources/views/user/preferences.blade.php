<x-layout>
    <x-slot:title>{{ trans('messages.preferences') }}</x-slot:title>

    <form action="{{ route('user.save-preferences') }}" method="POST" class="form-">
        @csrf

        <label class="form-label" for="locale">{{ trans('messages.language') }}</label>
        <select name="locale" id="locale">
            @foreach($locales as $locale => $name)
                <option
                    value="{{ $locale }}"
                    @selected(old('locale', $user->locale) === $locale)
                >
                    {{ $name }}
                </option>
            @endforeach
        </select>

        <p>
            <x-form.button class="btn-primary">{{ trans('messages.save') }}</x-form.button>
        </p>
    </form>
</x-layout>
