@extends('layouts.admin')
@push('js')
    <script>bulkTranslationConfig = @json($jsConfig)</script>
    @foreach($scripts as $script)
        <script src="{{$script}}"></script>
    @endforeach
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
                            Refresh translations in content for <code>{{ $library->getLibraryString(true) }}</code> and <code>{{$languageCode}}</code> language
                        </h3>
                    </div>
                    <div id="bulk-container">
                        <div class="panel-body row">
                            <p>
                                This goes through the content and updates existing translations in the common fields
                                stored in the content.
                                <br>
                                When creating/editing these are the texts that are available in the
                                "Text overrides and translations" section in the editor. If any of these texts was
                                changed when the content was created/modified, these changes will be lost.
                            </p>
                            <p>
                                Translations from other content types or libraries used in the content will also be updated.
                            </p>
                            <p>
                                The content is replaced, i.e. it will not be stored as new content nor will a version be created.
                                Timestamps will not be changed.
                            </p>
                        </div>
                        <div class="panel-body row">
                            <p>
                                Content that will be processed: {{$contentCount}}
                            </p>
                            <a
                                class="btn btn-danger disabled"
                                id="startRefresh"
                                disabled="disabled"
                            >
                                Start
                            </a>
                            <a
                                class="btn btn-default disabled"
                                id="cancelRefresh"
                                disabled="disabled"
                            >
                                Cancel
                            </a>
                        </div>
                        <div class="progress hidden" data-total="{{$contentCount}}" data-inprogress="0" data-success="0" data-failed="0">
                            <div class="progress-bar progress-bar-success"></div>
                            <div class="progress-bar progress-bar-warning progress-bar-striped"></div>
                            <div class="progress-bar progress-bar-danger"></div>
                        </div>
                        <pre class="bulk-update-log hidden"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
