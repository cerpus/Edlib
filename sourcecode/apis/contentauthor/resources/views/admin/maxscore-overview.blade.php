@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Libraries that don't have calculated max score</div>
                        <div id="h5p-admin-container"></div>
                        <ul class="list-group">
                        @forelse($libraries as $library)
                            <li class="list-group-item" id="library_{{str_replace('.', '', $library->name)}}">
                                <label>
                                    <input value="{{$library->name}}" type="checkbox" name="calculateLibrary" class="maxScoreCheckbox"  aria-checked="true" checked>
                                    <span>{{$library->title}}</span>
                                </label>
                                <span class="badge">{{$library->contents_count}}</span>
                                <div class="progress hidden" data-inprogress="0" data-success="0" data-failed="0">
                                    <div class="progress-bar progress-bar-success"></div>
                                    <div class="progress-bar progress-bar-warning progress-bar-striped"></div>
                                    <div class="progress-bar progress-bar-danger"></div>
                                </div>
                            </li>
                        @empty
                            No libraries found
                        @endforelse
                        </ul>
                        <div class="panel-footer">
                            <a class="btn btn-primary disabled" id="runCalculations">Start</a>
                            <a class="btn btn-primary @if($numFailed === 0) disabled @endif pull-right" id="failedCalculations" href="{{route('admin.maxscore.failed')}}">See failed</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    const CalculateScoreConfig = {!! $scoreConfig !!};
    const H5PIntegration = {!! $settings !!};
</script>
@endpush

@foreach($scripts as $script)
    @push('js')
        <script src="{{$script}}"></script>
    @endpush
@endforeach
