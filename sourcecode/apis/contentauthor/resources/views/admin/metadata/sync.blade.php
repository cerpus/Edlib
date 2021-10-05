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
                    <a href="{{ route("admin.metadataservice.doSync") }}" class="btn btn-success btn-block">
                        Sync with metadataservice
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

