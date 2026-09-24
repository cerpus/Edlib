@props(['displayItems'])

@foreach ($displayItems as $content)
    <x-content.action-buttons :$content :oob="true" />
@endforeach
