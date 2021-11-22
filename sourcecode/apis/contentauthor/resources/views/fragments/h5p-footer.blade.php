<script src="{{ mix('h5p-core-bundle.js') }}"></script>
@foreach( $jsScripts as $js)
    {!! HTML::script($js) !!}
@endforeach
{!! HTML::script('js/listener.js') !!}
<script>
    H5P.jQuery.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
@if(config('app.env') === 'production')
    {!! resolve(\App\Libraries\H5P\Interfaces\H5PAdapterInterface::class)->addTrackingScripts() !!}
@endif
</body>
</html>
