@extends('layouts.resource')

@push('configJs')
    <script type="text/javascript">
        window.CKEDITOR_BASEPATH = '/js/ckeditor/';
    </script>
    @foreach( $configJs as $js)
        {!! HTML::script($js) !!}
    @endforeach
@endpush

@push('js')
    <script src="/js/ckeditor/ckeditor.js"></script>
    <script src="{{ mix('react-h5p.js') }}"></script>
    {!! $config !!}
    {!! $adminConfig !!}
    @foreach( $jsScript as $js)
        {!! HTML::script($js) !!}
    @endforeach
@endpush

@push('css')
    <link rel="stylesheet" href="{{ mix('react-h5p.css') }}">
    @if(isset($styles))
        @foreach( $styles as $css)
            {!! HTML::style($css) !!}
        @endforeach
    @endif
@endpush

