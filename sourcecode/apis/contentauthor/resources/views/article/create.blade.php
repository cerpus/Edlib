@extends('layouts.resource')

@push('configJs')
    <script type="text/javascript">
        window.CKEDITOR_BASEPATH = '/js/ckeditor/';
        const ArticleIntegration = JSON.parse(@json($config));
        const uploadUrl = '{{ route('article-upload.new') }}';
    </script>
@endpush
@push('js')
    <script src="/js/ckeditor/ckeditor.js"></script>
    <script src="{{ mix('js/react-article.js') }}"></script>
@endpush

@push('css')
    <link rel="stylesheet" href="{{ mix('css/ckeditor_popup.css') }}">
    <link rel="stylesheet" href="{{ mix('css/article.css') }}">
@endpush
