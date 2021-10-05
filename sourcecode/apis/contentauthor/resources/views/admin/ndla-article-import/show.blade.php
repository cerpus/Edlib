@extends('layouts.admin')


@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">NDLA Article: {{ $article->title->title ?? '-- No title --' }}
                        <span class="pull-right">
                        @foreach($article->tags->tags as $tag)
                                <span class="badge">{{ $tag }}</span>
                            @endforeach
                            <?php
                            $oldUrl = $article->oldNdlaUrl ?? '#';
                            $oldUrl = str_replace('//', 'http://', $oldUrl);
                            $oldUrl = str_replace('red.', '', $oldUrl);
                            ?>
                            <a href="{{ $oldUrl  }}" target="_blank"><i class="fa fa-external-link"></i></a>
                        </span>
                    </div>
                    <div class="panel-body">
                        <strong>{!! $article->introduction->introduction ?? '-- No introduction --' !!}</strong>
                        @if($article->metaImage->url ?? null)
                            <img src="{{ $article->metaImage->url }}" alt="{{$article->metaImage->alt}}" width="100%">
                        @endif
                        {!! $article->content->content ?? '-- No content --' !!}
                        <div>License: {{$article->copyright->license->license}}</div>
                        <div>Language: {{$article->content->language}}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">RAW JSON</div>
                    <div class="panel-body">
                        <pre id="json-here"></pre>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let data = {!! json_encode($article, JSON_PRETTY_PRINT) !!};
            document.getElementById('json-here').innerHTML = '<code>' + JSON.stringify(data, 'unknown', 2) + '</code>';
            console.log(data);
        </script>
@endsection
