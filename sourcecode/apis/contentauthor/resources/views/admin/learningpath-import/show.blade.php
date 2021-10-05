@extends('layouts.admin')


@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Learning path: {{ $learningPath->title ?? '-- No title --' }}
                        <span class="pull-right">
                        @forelse($learningPath->json->tags->tags ?? [] as $tag)
                                <span class="badge">{{ $tag }}</span>
                            @empty
                                -- No tags --
                            @endforelse
                        </span>
                    </div>
                    <div class="panel-body">
                        <p>{!! $learningPath->json->introduction->introduction !!}</p>
                        @if($learningPath->json->coverPhotoUrl ?? null)
                            <p><img src="{{ $learningPath->json->coverPhotoUrl }}" width="100%"></p>
                        @endif
                        @forelse($learningPath->steps ?? [] as $step)
                            <p>
                                [{{ $step->order }}][{{ $step->json->type ?? '-' }}]
                                {{ $step->title ?? 'no title' }}<br>
                            <blockquote>{!! $step->json->description->description ?? 'no description' !!}</blockquote>
                            <br>
                            {!! ($step->json->embedUrl->url ?? false) ?('<a href="'.$step->json->embedUrl->url.'" target="_blank">'.$step->json->embedUrl->url.'</a>'): 'no url' !!}
                            </p>
                        @empty
                            <p><h3>No Learning Steps</h3></p>
                        @endforelse

                        <div>License: {{$learningPath->json->copyright->license->license ?? 'No license info'}}</div>
                        <div>Language: {{$learningPath->json->title->language ?? 'No language info'}}</div>
                    </div>
                </div>
            </div>
        </div>
@endsection
