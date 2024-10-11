@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2>
                            Max score calculation
                        </h2>
                        <h4>
                            Libraries that supports max score, and have content where max score is not calculated
                        </h4>
                        <div class="alert alert-danger">
                            If a library uses other libraries that can give a score, these libraries should have a
                            pre-save script. The calculation will not fail if the script is missing, but the
                            calculation might not give the expected result.
                        </div>
                    </h3>
                    <div id="h5p-admin-container"></div>
                        <ul class="list-group">
                        @forelse($libraries as $library)
                            <li class="list-group-item" id="library_{{$library->id}}">
                                <label>
                                    <input value="{{$library->id}}" type="checkbox" name="calculateLibrary" class="maxScoreCheckbox" aria-checked="true" checked>
                                    <span>{{sprintf('%s (%s)', $library->getLibraryString(true), $library->title)}}</span>
                                </label>
                                <span class="badge">{{$library->contents_count}}</span>
                                <div class="progress hidden" data-inprogress="0" data-success="0" data-failed="0">
                                    <div class="progress-bar progress-bar-success"></div>
                                    <div class="progress-bar progress-bar-warning progress-bar-striped"></div>
                                    <div class="progress-bar progress-bar-danger"></div>
                                </div>
                                <div class="errorlog hidden"></div>
                            </li>
                        @empty
                            No libraries found
                        @endforelse
                        </ul>
                        <div class="panel-footer" style="display:flex">
                            <label style="flex-grow: 1">
                                Libraries: {{count($libraries)}}
                            </label>
                            <div class="badge" style="flex-grow: 0; align-self: flex-end">{{$libraries->sum('contents_count')}}</div>
                        </div>
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
    const H5PLibraryPath = "{!! $libraryPath !!}";
</script>
@endpush

@foreach($scripts as $script)
    @push('js')
        <script src="{{$script}}"></script>
    @endpush
@endforeach
