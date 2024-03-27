@extends('layouts.article-show')

@if(!empty($customCSS))
@section('customCSS')
    <link rel="stylesheet" href="{{ $customCSS }}">
@endsection
@endif

@section('title') {{ $article->title }} @endsection

@section('content')
    {!! $article->render() !!}
@endsection
