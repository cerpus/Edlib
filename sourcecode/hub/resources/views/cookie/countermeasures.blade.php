{{-- TODO: translate, improve the user experience --}}
@php use Illuminate\Support\Facades\Vite; @endphp
<x-layout :nav="false">
    <x-slot:title>Requesting storage access</x-slot:title>

    <noscript>
        <p>You must have JavaScript enabled to open Edlib.</p>
    </noscript>

    <p class="alert alert-danger mb-3" id="permission-error" hidden>
        You must grant storage access to Edlib.
    </p>

    <p id="step1" hidden>
        <a
            href="{{ route('cookie.popup') }}"
            class="btn btn-primary"
            rel="opener"
            target="_blank"
        >
            Start opening Edlib
        </a>
    </p>

    <p id="step2" hidden>
        <button class="btn btn-primary" id="launch">Open Edlib</button>
    </p>

    <form action="{{ $url }}" method="{{ $method }}" name="launch_form">
        @foreach ($parameters as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
    </form>

    <script nonce="{{ Vite::cspNonce() }}">
        addEventListener('message', function (event) {
            if (event.data === 'ready') {
                document.getElementById('step1').hidden = true;
                document.getElementById('step2').hidden = false;
            } else {
                console.log("Unknown message?", event);
            }
        });

        document.getElementById('launch').addEventListener('click', function () {
            document.requestStorageAccess().then(function () {
                document.forms.launch_form.submit();
            }).catch(function () {
                document.getElementById('permission-error').hidden = false;
            });
        });

        document.hasStorageAccess().then(function (granted) {
            if (granted) {
                document.forms.launch_form.submit();
            } else {
                document.getElementById('step1').hidden = false;
            }
        }).catch(function (e) {
            console.error('document.hasStorageAccess failed for some reason?', e);

            // If this should ever happen, there's little the user can do, but
            // show an error anyway
            document.getElementById('permission-error').hidden = false;
        });
    </script>
</x-layout>
