@extends('layouts.resource', ['formSurroundsMainContent' => true])

@push('js')
    <script src="{{ mix("js/react-embed.js") }}"></script>
    <script src="{{ mix("js/link-editor.js") }}"></script>
@endpush

@push('css')
    <link rel="stylesheet" href="{{ mix('css/link.css') }}">
@endpush
