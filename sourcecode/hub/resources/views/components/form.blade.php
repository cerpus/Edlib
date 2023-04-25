<form {{ $attributes->except('csrf')->merge(['action' => '', 'method' => 'POST']) }}>
    @if ($csrf ?? true)
        @csrf
    @endif

    {{ $slot }}
</form>
