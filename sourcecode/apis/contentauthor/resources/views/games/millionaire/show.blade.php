@extends('layouts.game')

@section('title', $title)
@section('basePath', $basePath)
@section('language', $language)

@push('linked')
    @foreach( $linked as $link)
<link rel="{{$link->rel}}" href="{{$link->href}}"@if(isset($link->type)) type="{{$link->type}}"@endif @if(isset($link->sizes)) sizes="{{$link->sizes}}"@endif>
    @endforeach
@endpush

@push('css')
    @foreach( $css as $src)
<link rel="stylesheet" href="{{$src}}">
    @endforeach
@endpush

@push('js')
    <script>
        var gameObject = {!!$gameSettings!!};
        var language = '{{$language}}';
    </script>
    @foreach( $scripts as $js)
<script src="{{$js}}"></script>
    @endforeach
@endpush
