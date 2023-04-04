<div class="mb-3">
    <label for="{{ $name }}">{{ $label ?? $name }}</label>

    @switch($type ?? 'text')
        @case('text')
        @case('password')
        @case('search')
            <x-form.input name="{{ $name }}" type="{{ $type ?? 'text' }}"/>
            @break
    @endswitch
</div>
