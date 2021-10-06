<script src="{{ elixir('bootstrap.js') }}"></script>
<script src="{{ elixir('react-vendor.js') }}"></script>
<script src="{{ elixir('react-components.js') }}"></script>
<script src="{{ elixir('react-app.js') }}"></script>
{!! $config !!}
@foreach( $jsScript as $js)
{!! HTML::script($js) !!}
@endforeach
@stack('js')
</body>
</html>
