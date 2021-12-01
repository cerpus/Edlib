@extends('layouts.resource')

@push('configJs')
    <script type="text/javascript">
        var CKEDITOR_BASEPATH = '/js/ckeditor/';
        const ArticleIntegration = JSON.parse(@json($config));
        const uploadUrl = '{{ route('article-upload.existing', $article->id) }}';
    </script>
@endpush

@push('js')
    <script src="{{ mix('js/react-article.js') }}"></script>
    <link rel="stylesheet" href="{{ mix('js/article.js') }}">
@endpush

@push('css')
    <link rel="stylesheet" href="{{ mix('css/ckeditor_popup.css') }}">
@endpush
