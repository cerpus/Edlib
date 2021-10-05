@extends('layouts.resource', ['formSurroundsMainContent' => true])

@push('js')
    <script src="{{ elixir("react-embed.js") }}"></script>
    <script src="{{ elixir("js/link-editor.js") }}"></script>
@endpush

@push('css')
    <link rel="stylesheet" href="{{ elixir('react-embed.css') }}">
    <link rel="stylesheet" href="{{ elixir('link.css') }}">
@endpush
