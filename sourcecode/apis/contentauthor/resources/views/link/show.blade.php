@extends('layouts.link-show')

@if(!empty($customCSS))
@section('customCSS')
    <link rel="stylesheet" href="{{ $customCSS }}">
@endsection
@endif

@section('title') {{ $link->title }} @endsection

@section('content')
    @if($metadata !== null)
        <a href="{{$link->link_url}}" target="_blank">
            <div class="linkTextContainer">
                <h4>{{$metadata->title}}</h4>
                @if (!empty($metadata->image))
                    <img class="linkImage" src="{{$metadata->image}}" />
                @endif
            </div>
        </a>
        <div class="provider">{{$metadata->providerName}} <span>({{$metadata->providerUrl}})</span></div>
    @elseif($link->link_text != "")
        <a target="_blank" href="{{ $link->link_url }}">{{$link->link_text }}</a>
    @else
        <a target="_blank" href="{{ $link->link_url }}">{{$link->link_url }}</a>
    @endif
@endsection