@php
    use App\Enums\LtiToolEditMode;
    $tool ??= null;
    $editing = $tool !== null;
@endphp

<x-form {{ $attributes->only(['action', 'method']) }}>
    {{-- Fake fields for password managers to fill --}}
    <div class="visually-hidden" aria-hidden="true">
        <input tabindex="-1">
        <input type="password" autocomplete="new-password" tabindex="-1">
    </div>

    <x-form.field
        name="name"
        required
        :label="trans('messages.name')"
        :value="$tool?->name"
    />

    <x-form.field
        name="slug"
        :label="trans('messages.url-slug')"
        :value="$tool?->slug"
    />

    <x-form.field
        name="creator_launch_url"
        inputmode="url"
        required
        :label="trans('messages.lti-launch-url')"
        :value="$tool?->creator_launch_url"
    />

    <x-form.field
        name="consumer_key"
        autocomplete="off"
        required
        :label="trans('messages.key')"
        :value="$tool?->consumer_key"
    />

    <x-form.field
        name="consumer_secret"
        type="password"
        :required="!$editing"
        :label="trans('messages.secret')"
    />

    <fieldset>
        <legend>{{ trans('messages.edit-mode') }}</legend>

        <x-form.field
            name="edit_mode"
            value="{{ LtiToolEditMode::Replace }}"
            type="radio"
            :checked="!$editing || $tool->edit_mode === LtiToolEditMode::Replace"
            :label="trans('messages.edit-mode-replace')"
            :text="trans('messages.edit-mode-replace-help')"
        />

        <x-form.field
            name="edit_mode"
            value="{{ LtiToolEditMode::DeepLinkingRequestToContentUrl }}"
            type="radio"
            :checked="$tool?->edit_mode === LtiToolEditMode::DeepLinkingRequestToContentUrl"
            :label="trans('messages.edit-mode-deep-linking-request-to-content-url')"
            :text="trans('messages.edit-mode-deep-linking-request-to-content-url-help')"
        />
    </fieldset>

    <fieldset>
        <legend>{{ trans('messages.privacy-settings') }}</legend>

        <x-form.field
            name="send_name"
            type="checkbox"
            :checked="$tool?->send_name ?? false"
            :label="trans('messages.send-full-name-to-lti-tool', ['site' => config('app.name')])"
        />

        <x-form.field
            name="send_email"
            type="checkbox"
            :checked="$tool?->send_email ?? false"
            :label="trans('messages.send-email-to-lti-tool', ['site' => config('app.name')])"
        />
    </fieldset>

    {{ $button }}
</x-form>
