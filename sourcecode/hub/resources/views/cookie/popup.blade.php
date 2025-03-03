{{-- TODO: translate, improve the user experience --}}
@php use Illuminate\Support\Facades\Vite; @endphp
<x-layout>
    <x-slot:title>Setting the cookie</x-slot:title>

    <x-slot:head>
        <meta name="robots" content="noindex">
    </x-slot:head>

    <button class="btn btn-primary" id="continue">Continue opening Edlib</button>

    <script nonce="{{ Vite::cspNonce() }}">
        if (!window.opener) {
            location.href = {!! json_encode(route('home')) !!};
        }

        document.getElementById('continue').addEventListener('click', function () {
            opener.postMessage('ready', '*');
            self.close();
        });
    </script>
</x-layout>
