@extends('layouts.admin')

@inject('h5pInterface', 'App\Libraries\H5P\Interfaces\H5PAdapterInterface')

@section('content')
    @if($h5pInterface->showNorgesfilmAdmin())
        @if(session()->has('message'))
            <div class="row">
                <div class="alert alert-info">{!! session('message')  !!}</div>
            </div>
        @endif

        <div class="row text-center">
            <h2><a href="{{ route('admin.norgesfilm.index') }}"><i class="glyphicon glyphicon-backward"></i> Back</a>
            </h2>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-6">
                    @include('admin.norgesfilm.history-view.render-history', ['versions' => $versions])
                </div>
                <div class="col-md-6">

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 ">
                <div class="col-md-6">
                    <span class="text-center">Local</span>
                    <iframe id="localIframe" src="{{ $norgesfilm->article_url }}"
                            style="width:100%; height: 100vh; "></iframe>
                </div>
                <div class="col-md-6">
                    <span class="text-center">NDLA</span>
                    <iframe src="{{ $norgesfilm->ndla_url ?? route('admin.norgesfilm.ndla-url-not-found') }}"
                            style="width:100%; height: 100vh;"></iframe>
                </div>
            </div>
        </div>
    @else
        <h1>Go away!</h1>
    @endif
@endsection

@push('styles')
    <style>
        iframe {
            overflow: scroll !important;
        }
    </style>
@endpush



