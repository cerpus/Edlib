@php
use App\Enums\LtiToolEditMode;
@endphp

<x-layout>
    <x-slot:title>Add LTI tool</x-slot:title>

    <x-form action="{{ route('admin.lti-tools.store') }}">
        {{-- Fake fields for password managers to fill --}}
        <div class="visually-hidden" aria-hidden="true">
            <input tabindex="-1">
            <input type="password" autocomplete="new-password" tabindex="-1">
        </div>

        <x-form.field
            name="name"
            required
            :label="trans('messages.name')"
        />

        <x-form.field
            name="creator_launch_url"
            inputmode="url"
            required
            :label="trans('messages.lti-launch-url')"
        />

        <x-form.field
            name="consumer_key"
            autocomplete="off"
            required
            :label="trans('messages.key')"
        />

        <x-form.field
            name="consumer_secret"
            type="password"
            required
            :label="trans('messages.secret')"
        />

        <fieldset>
            <legend>{{ trans('messages.launch-settings') }}</legend>

            <x-form.field
                name="proxy_launch"
                type="checkbox"
                :label="trans('messages.proxy-launch-to-lti-tool', ['site' => config('app.name')])"
                :text="trans('messages.proxy-launch-to-lti-tool-help', ['site' => config('app.name')])"
            />
        </fieldset>

        <fieldset>
            <legend>{{ trans('messages.edit-mode') }}</legend>

            <x-form.field
                name="edit_mode"
                value="{{ LtiToolEditMode::Replace }}"
                type="radio"
                checked
                :label="trans('messages.edit-mode-replace')"
                :text="trans('messages.edit-mode-replace-help')"
            />

            <x-form.field
                name="edit_mode"
                value="{{ LtiToolEditMode::DeepLinkingRequestToContentUrl }}"
                type="radio"
                :label="trans('messages.edit-mode-deep-linking-request-to-content-url')"
                :text="trans('messages.edit-mode-deep-linking-request-to-content-url-help')"
            />
        </fieldset>

        <fieldset>
            <legend>{{ trans('messages.privacy-settings') }}</legend>

            <x-form.field
                name="send_name"
                type="checkbox"
                :label="trans('messages.send-full-name-to-lti-tool', ['site' => config('app.name')])"
            />

            <x-form.field
                name="send_email"
                type="checkbox"
                :label="trans('messages.send-email-to-lti-tool', ['site' => config('app.name')])"
            />
        </fieldset>

        <x-form.button class="btn-primary">{{ trans('messages.add') }}</x-form.button>
    </x-form>
</x-layout>
