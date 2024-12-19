@php
    $platform ??= null;
    $editing = $platform !== null;
@endphp

<x-form action="{{ $action }}" method="{{ $method }}">
    <div class="alert alert-info pb-0">
        <p>{{ trans('messages.lti-platforms-common-values') }}</p>
        <p>
            {{ trans('messages.lti-platform-selection-url') }}: <kbd class="user-select-all">{{ route('lti.select', [\App\Support\SessionScope::TOKEN_PARAM => null]) }}</kbd><br>
            {{ trans('messages.lti-version') }}: <kbd>1.0</kbd> / <kbd>1.1</kbd> / <kbd>1.2</kbd><br>
        </p>
    </div>

    <x-form.field
        name="name"
        type="text"
        :label="trans('messages.name')"
        :value="$platform?->name"
    />

    <fieldset aria-describedby="dangerous-stuff">
        <p class="alert alert-danger" id="dangerous-stuff">
            {{ trans('messages.dangerous-lti-platform-settings') }}
        </p>

        <x-form.field
            name="enable_sso"
            type="checkbox"
            :label="trans('messages.lti-platform-enable-sso')"
            :text="trans('messages.lti-platform-enable-sso-help', ['site' => config('app.name')])"
            :checked="$platform?->enable_sso"
        />

        <x-form.field
            name="authorizes_edit"
            type="checkbox"
            :label="trans('messages.lti-platform-authorizes-edit')"
            :text="trans('messages.lti-platform-authorizes-edit-help', ['site' => config('app.name')])"
            :checked="$platform?->authorizes_edit"
        />
    </fieldset>

    {{ $button }}
</x-form>

