@php use Illuminate\Support\Facades\Vite; @endphp
<x-layout no-nav no-footer>
    <x-slot:title>Resize test</x-slot:title>

    <button class="resize-button" data-size="640">Resize to 640</button>
    <button class="resize-button" data-size="800">Resize to 800</button>

    <script nonce="{{ Vite::cspNonce() }}">
        document.querySelectorAll('.resize-button').forEach(button => {
            button.addEventListener('click', event => {
                event.preventDefault();

                parent.postMessage({
                    action: 'resize',
                    scrollHeight: Number(button.getAttribute('data-size')),
                }, '*');
            });
        });
    </script>
</x-layout>
