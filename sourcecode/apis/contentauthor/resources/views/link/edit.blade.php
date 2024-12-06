@extends('layouts.resource', ['formSurroundsMainContent' => true])

@section('title', trans('link.link'))
@section('header', trans('link.header'))
@section('headerInfo', trans('link.edit-link'))

@section('form_open')
    <form action="{{ route('link.update', $link->id) }}" method="POST" id="content-form">
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="redirectToken" value="{{ $redirectToken }}">
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

    <div class="linkcontent-container" id="linkcontent-container" data-link-metadata="{{$link->externalData}}"
         data-link="{{$link}}"></div>

    <a href="{{ route('lti.container') }}"
       id="cex"
       data-module-id="{{$link->id}}"
       class="activity-list-item-new-anchor"
       data-popup-width="-50"
       data-popup-height="-50"
    ></a>
@endsection

@push("js")
    <script src="{{ mix("js/link-editor.js") }}"></script>
    @include('fragments.js.edit-unlock', ['content' => $link])
@endpush

@push('css')
    <link rel="stylesheet" href="{{ mix('css/link.css') }}">
@endpush
