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
                    <div class="panel-heading">Replace ref with video id</div>
                    <span>Total of H5Ps: {{$total}}</span>
                    <span>Processed: {{$processed}}</span>
                    <span>With ref: {{$targets}}</span>
                    <a href="{{ route("admin.video.ndla.reindexrefs") }}" class="btn btn-danger btn-block">
                        Drop targets
                    </a>
                    <a href="{{ route("admin.video.ndla.populatetargets") }}" class="btn btn-info btn-block">
                        Index targets
                    </a>
                    <a href="{{ route("admin.video.ndla.doreplaceref") }}" class="btn btn-success btn-block">
                        Replace ref with videoid(NDLA)
                    </a>

                    @if(!empty($targetList))
                        @foreach($targetList as $target)
                            <div>{{$target->content_id}} - {{$target->title}} ({{$target->processed ? 'Sendt til kø' : 'Klar til sending'}})</div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

