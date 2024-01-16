@extends('layouts.admin')
@push('js')
    <script>contentLanguageConfig = @json($config)</script>
    @foreach($scripts as $script)
        <script src="{{$script}}"></script>
    @endforeach

    <script src="{{ mix('js/ndla-content-language.js') }}"></script>
@endpush
@section ('content')
    <div class="container" style="width:80vw;">
        <a href="{{ route('admin.update-libraries') }}">Library list</a>
        <br>
        <a href="{{ route('admin.check-library', [$library->id]) }}">Library details</a>
        <br>
        <a href="{{ route('admin.library-translation', [$library->id, $languageCode]) }}">Library "{{$languageCode}}" translation</a>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>
                            Refresh translations saved with content for {{ $library->getLibraryString(true) }} and "{{$languageCode}}" language
                        </h3>
                    </div>
                    <div id="bulk-container">
                        <div class="panel-body row">
                            {{$contentCount}} content will be updated
                            <a class="btn btn-primary disabled" id="startRefresh">Start</a>
                        </div>
                        <div class="progress hidden" data-total="{{$contentCount}}" data-inprogress="0" data-success="0" data-failed="0">
                            <div class="progress-bar progress-bar-success"></div>
                            <div class="progress-bar progress-bar-warning progress-bar-striped"></div>
                            <div class="progress-bar progress-bar-danger"></div>
                        </div>
                        <div class="errorlog hidden"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @dump($config)
@endsection
