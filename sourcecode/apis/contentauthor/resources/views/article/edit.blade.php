@extends('layouts.resource')

@push('configJs')
    <script type="text/javascript">
        var CKEDITOR_BASEPATH = '/js/ckeditor/';
        const ArticleIntegration = JSON.parse(@json($config));
        const uploadUrl = '{{ route('article-upload.existing', $article->id) }}';
    </script>
@endpush
@push('js')
    <script src="{{ mix('react-article.js') }}"></script>
@endpush

@push('css')
    <link rel="stylesheet" href="{{ mix('react-article.css') }}">
    <link rel="stylesheet" href="{{ mix('ckeditor_popup.css') }}">
    <link rel="stylesheet" href="{{ mix('article.css') }}">
@endpush
