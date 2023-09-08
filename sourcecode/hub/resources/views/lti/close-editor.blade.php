<x-layout :nav="false">
    <x-slot:title>Closing Edlib</x-slot:title>

    {{-- FIXME: if $redirectUrl should include form params, then they must be included as hidden inputs --}}
    <form action="{{ $redirectUrl }}" method="GET" class="auto-submit" target="_parent">
        <button class="btn btn-primary">Return to Edlib</button>
    </form>
</x-layout>
