@extends('layouts.admin')

@push('js')
    <script>H5PAdminIntegration ={!! json_encode($h5pAdminIntegration) !!}</script>
    <script>H5PIntegration ={!! json_encode($h5pIntegration) !!}</script>
    @foreach($scripts as $script)
        <script src="{{$script}}"></script>
    @endforeach
@endpush

@push('styles')
    @foreach($styles as $style)
        <link href="{{$style}}" rel="stylesheet">
    @endforeach
@endpush

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Update content</div>
                    <div class="panel-body">
                        <div id="h5p-admin-container">Please enable Javascript</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
