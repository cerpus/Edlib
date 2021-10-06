@extends('layouts.resource')

@push('configJs')
    <script type="text/javascript">
        var CKEDITOR_BASEPATH = '/js/ckeditor/';
        const ArticleIntegration = JSON.parse(@json($config));
        const uploadUrl = '{{ route('article-upload.existing', $article->id) }}';
    </script>
@endpush
@push('js')
    <script src="{{ elixir('react-article.js') }}"></script>
@endpush

@push('css')
    <link rel="stylesheet" href="{{ elixir('react-article.css') }}">
    <link rel="stylesheet" href="{{ elixir('ckeditor_popup.css') }}">
    <link rel="stylesheet" href="{{ elixir('article.css') }}">
@endpush
