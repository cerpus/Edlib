@php
use App\Models\LtiToolEditMode;
use App\Models\LtiVersion;
@endphp

<x-layout>
    <x-slot:title>Add LTI tool</x-slot:title>

    <form action="{{ route('admin.lti-tools.store') }}" method="POST">
        @csrf

        @if ($errors->any())
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        @endif

        <p>
            <label>
                Name
                <input type="text" name="name" required>
            </label>
        </p>

        <p>
            <label>
                LTI version
                <select name="lti_version">
                    <option value="" selected>Pick one…</option>
                    <option value="{{ LtiVersion::Lti1_1->value }}">1.1</option>
                    <option value="{{ LtiVersion::Lti1_3->value }}">1.3</option>
                </select>
            </label>
        </p>

        <p>
            <label>
                Creator launch URL
                <input type="text" inputmode="url" name="creator_launch_url" required>
            </label>
        </p>

        <p>
            <label>
                Consumer key
                <input type="text" name="consumer_key">
            </label>
        </p>

        <p>
            <label>
                Consumer secret
                <input type="password" name="consumer_secret">
            </label>
        </p>

        <fieldset>
            <legend>{{ trans('messages.edit-mode') }}</legend>

            <div class="position-relative form-check">
                <label class="form-check-label stretched-link">
                    <input
                        type="radio"
                        name="edit_mode"
                        value="{{ LtiToolEditMode::Replace }}"
                        aria-describedby="edit-mode-replace-help"
                        class="form-check-input"
                        @checked(old('edit_mode', true))
                    >
                    <b>{{ trans('messages.edit-mode-replace') }}</b>
                </label>
                <p class="form-text" id="edit-mode-replace-help">
                    {{ trans('messages.edit-mode-replace-help') }}
                </p>
            </div>

            <div class="position-relative form-check">
                <label class="form-check-label stretched-link">
                    <input
                        type="radio"
                        name="edit_mode"
                        value="{{ LtiToolEditMode::DeepLinkingRequestToContentUrl }}"
                        aria-describedby="edit-mode-deep-linking-request-to-content-url-help"
                        class="form-check-input"
                        @checked(old('edit_mode', false))
                    >
                    <b>{{ trans('messages.edit-mode-deep-linking-request-to-content-url') }}</b>
                </label>
                <p class="form-text" id="edit-mode-deep-linking-request-to-content-url-help">
                    {{ trans('messages.edit-mode-deep-linking-request-to-content-url-help') }}
                </p>
            </div>
        </fieldset>

        <fieldset>
            <legend>{{ trans('messages.privacy-settings') }}</legend>

            <div class="form-check">
                <label class="form-check-label">
                    <input
                        type="checkbox"
                        name="send_name"
                        value="1"
                        class="form-check-input"
                        @checked(old('send_name', false))
                    > {{ trans('messages.send-full-name-to-lti-tool', ['site' => config('app.name')]) }}
                </label>
            </div>

            <div class="form-check">
                <label class="form-check-label">
                    <input
                        type="checkbox"
                        name="send_email"
                        value="1"
                        class="form-check-input"
                        @checked(old('send_email', false))
                    > {{ trans('messages.send-email-to-lti-tool', ['site' => config('app.name')]) }}
                </label>
            </div>
        </fieldset>

        <p class="mt-3">
            <button class="btn btn-primary">{{ trans('messages.add') }}</button>
        </p>
    </form>
</x-layout>
