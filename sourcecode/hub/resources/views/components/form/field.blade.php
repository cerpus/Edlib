<div @class([
    $class ?? '',
    'mb-3',
    'form-check' => in_array($type ?? 'text', ['checkbox', 'radio']),
])>
    @switch ($type ?? 'text')
        @case('select')
            <x-form.label for="{{ $name }}" :required="$required ?? false">
                {{ $label ?? $name }}
            </x-form.label>
            <x-form.dropdown :attributes="$attributes->except(['class', 'label', 'text'])" />
            @break

        @case('radio')
        @case('checkbox')
            <label for="{{ $name }}" class="form-check-label">{{ $label ?? $name }}</label>
            <x-form.checkbox :attributes="$attributes->except(['class', 'label', 'text'])" />
            @break

        @default
            <x-form.label for="{{ $name }}" :required="$required ?? false">
                {{ $label ?? $name }}
            </x-form.label>
            <x-form.input :attributes="$attributes->except(['class', 'label', 'text'])" />
    @endswitch

    @if ($text ?? false)
        <div class="form-text">
            {{ $text }}
        </div>
    @endif

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
