@extends('layouts.resource', ['formSurroundsMainContent' => true])

@push('js')
    <script src="{{ mix("react-embed.js") }}"></script>
    <script src="{{ mix("js/link-editor.js") }}"></script>
@endpush

@push('css')
    <link rel="stylesheet" href="{{ mix('react-embed.css') }}">
    <link rel="stylesheet" href="{{ mix('link.css') }}">
@endpush
