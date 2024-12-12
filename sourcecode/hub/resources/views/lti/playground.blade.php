<x-layout>
    <x-slot:title>{{ trans('messages.lti-playground') }}</x-slot:title>

    @if ($ltiRequest)
        <iframe src="about:blank" width="640" height="480" name="launch_frame"></iframe>

        <form action="{{ $ltiRequest->getUrl() }}" method="POST" name="launch_form" target="launch_frame">
            {!! $ltiRequest->toHtmlFormInputs() !!}
        </form>

        <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
            document.forms.launch_form.submit();
        </script>

        <hr>
    @endif

    <x-form>
        {{-- Fake fields for password managers to fill --}}
        <div class="visually-hidden">
            <x-form.field name="dummy_username" tabindex="-1" />
            <x-form.field name="dummy_password" type="password" tabindex="-1" />
        </div>

        <x-form.field name="launch_url" required />
        <x-form.field name="key" />
        <x-form.field name="secret" type="password" />
        <x-form.field name="parameters" text="Form-encoded parameters"/>
        <x-form.field name="time" text="Leave blank for current date/time" />

        <x-form.button class="btn-primary">Launch</x-form.button>
    </x-form>
</x-layout>
