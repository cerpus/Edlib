@extends('layouts.admin')
@push('js')
    <script>H5PIntegration ={!! json_encode($h5pIntegration) !!}</script>
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
                    <div class="panel-body row">
                        {{$contentCount}} content will be updated
                        <button type="button" onclick="goGoGo()">Go</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function goGoGo() {
            new H5PEditor.ContentLanguageUpdateProcess(
                JSON.parse(@json($contents->parameters)),
                '',
                '{{ $library->getLibraryString(false) }}',
                '{{ $languageCode }}'
            );
        }
    </script>
    @dump($contents);
@endsection
