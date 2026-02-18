@extends('layouts.resource')

@push('configJs')
    <script type="text/javascript">
        window.CKEDITOR_BASEPATH = '/js/ckeditor/';
    </script>
    @foreach( $configJs as $js)
        <script src="{{ $js }}"></script>
    @endforeach
@endpush

@push('js')
    <script src="{{ mix('js/react-h5p.js') }}"></script>
    {!! $config !!}
    {!! $adminConfig !!}
    @foreach( $jsScript as $js)
        <script src="{{ $js }}"></script>
    @endforeach
@endpush

@push('css')
    @if(isset($styles))
        @foreach( $styles as $css)
            <link rel="stylesheet" href="{{ $css }}">
        @endforeach
    @endif
@endpush

