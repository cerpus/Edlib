<x-layout :nav="false">
    <x-slot:title>Closing Edlib</x-slot:title>

    <form action="{{ $url }}" method="{{ $method }}" class="auto-submit" target="_parent">
        @foreach ($parameters ?? [] as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <button class="btn btn-primary">Return to Edlib</button>
    </form>
</x-layout>
