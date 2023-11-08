<x-layout :nav="false">
    <x-slot:title>Redirecting</x-slot:title>

    <form action="{{ $url }}" method="{{ $method }}" class="auto-submit" target="{{ $target ?? '_self' }}">
        @foreach ($parameters ?? [] as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <button class="btn btn-primary">{{ trans('messages.continue') }}</button>
    </form>
</x-layout>
