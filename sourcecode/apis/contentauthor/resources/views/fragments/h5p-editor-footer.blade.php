<script src="{{ mix('bootstrap.js') }}"></script>
<script src="{{ mix('react-vendor.js') }}"></script>
<script src="{{ mix('react-components.js') }}"></script>
<script src="{{ mix('react-app.js') }}"></script>
{!! $config !!}
@foreach( $jsScript as $js)
{!! HTML::script($js) !!}
@endforeach
@stack('js')
</body>
</html>
