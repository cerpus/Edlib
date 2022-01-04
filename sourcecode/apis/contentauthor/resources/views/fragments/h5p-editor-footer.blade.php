<script src="{{ mix('js/bootstrap.js') }}"></script>
<script src="{{ mix('js/react-app.js') }}"></script>
{!! $config !!}
@foreach( $jsScript as $js)
{!! HTML::script($js) !!}
@endforeach
@stack('js')
</body>
</html>
