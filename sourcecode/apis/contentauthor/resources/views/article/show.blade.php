@extends('layouts.article-show')

@if(!empty($customCSS))
@section('customCSS')
    <link rel="stylesheet" href="{{ $customCSS }}">
@endsection
@endif

@section('title') {{ $article->title }} @endsection

@section('content')
    {!! $article->content  !!}
@endsection

@push('js')
    <script>
        const isPreview = @if(!empty($preview)) {{ $preview }} @else 0 @endif;
    </script>
@endpush