@extends('layouts.resource', ['formSurroundsMainContent' => true])

@section('title', trans('link.link'))
@section('header', trans('link.header'))
@section('headerInfo', trans('link.create-link'))

@section('form_open')
    {!! Form::open(['route' => 'link.store', 'method' => 'post', 'id' => 'content-form']) !!}
    {!! Form::hidden("redirectToken", $redirectToken) !!}
@endsection

@section("content")
    @if (isset($errors) && count($errors) > 0)
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="linkcontent-container" id="linkcontent-container"></div>
@endsection

@push('js')
    <script src="{{ mix("js/link-editor.js") }}"></script>
@endpush

@push('css')
    <link rel="stylesheet" href="{{ mix('link.css') }}">
@endpush
