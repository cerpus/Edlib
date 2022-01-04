@extends('layouts.resource')

@if($libName !== false)
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
        <script src="{{ mix('js/react-h5p.js') }}"></script>
        {!! $config !!}
        @foreach( $jsScript as $js)
            {!! HTML::script($js) !!}
        @endforeach
    @endpush

    @push('css')
        @if(isset($styles))
            @foreach( $styles as $css)
                {!! HTML::style($css) !!}
            @endforeach
        @endif
    @endpush
@endif
