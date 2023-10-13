<form {{ $attributes->except(['csrf', 'method'])->merge([
    'action' => '',
    'method' => strtoupper($method ?? 'POST') !== 'GET' ? 'POST' : 'GET',
]) }}>
    @if (!in_array(strtoupper($method ?? 'POST'), ['GET', 'POST']))
        <input type="hidden" name="_method" value="{{ $method }}">
    @endif

    @if ($csrf ?? true)
        @csrf
    @endif

    {{ $slot }}
</form>
