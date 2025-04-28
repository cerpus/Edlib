@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Admin</div>
                    <div class="panel-body">
                        @if (\Illuminate\Support\Facades\Session::has('message'))
                            <div class="alert alert-info">
                                {{ \Illuminate\Support\Facades\Session::get('message') }}
                            </div>
                        @endif

                        <a class="col-md-4 well well-lg" href="{{ route('admin.capability') }}">
                            <i class="glyphicon glyphicon-edit"></i> Capabilities
                        </a>
                        <a class="col-md-4 well well-lg" href="{{ route('admin.update-libraries') }}">
                            <i class="glyphicon glyphicon-upload"></i> Manage H5P content types
                        </a>
                        <a class="col-md-4 well well-lg" href="{{ route('admin.games') }}">
                            <i class="glyphicon glyphicon-upload"></i> Update games
                        </a>

                        <form action="{{ route('admin.clear-cache') }}" method="POST">
                            @csrf

                            <button class="btn btn-danger">{{ trans('admin.clear-cache') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection
