@extends('layouts.admin')

@inject('h5pInterface', 'App\Libraries\H5P\Interfaces\H5PAdapterInterface')

@section('content')
    @if($h5pInterface->showNorgesfilmAdmin())
        <div class="container">
            @if(session()->has('message'))
                <div class="row">
                    <div class="alert alert-info">{{ session('message') }}</div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel panel-default">
                        <div class="panel-heading">NDLA URL not found

                        </div>
                        <div class="panel-body">
                            <h1>NDLA URL not found</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <h1>This is not the environment i expected!</h1>
    @endif
@endsection
