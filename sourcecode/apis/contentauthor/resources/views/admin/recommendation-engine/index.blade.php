@extends('layouts.admin')

@section('content')
    <div class="container">
        @if(session()->has('message'))
            <div class="row">
                <div class="alert alert-info">{{ session('message') }}</div>
            </div>
        @endif
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Bulk index</div>
                    <a href="{{ route("admin.recommendation-engine.doIndex") }}" class="btn btn-success btn-block">
                        Index all content in Recomendation Engine
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Bulk index NDLA Articles</div>
                    <a href="{{ route("admin.recommendation-engine.index-ndla-articles") }}"
                       class="btn btn-success btn-block">
                        Index all NDLA articles in Recomendation Engine
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Bulk index status</div>
                    Num to index: {{ $indexNum }}
                </div>
            </div>
        </div>
    </div>
@endsection

