<select {{ $attributes->except(['selected', 'options'])->class(['form-select']) }}>
    @if ($emptyOption ?? false)
        <option value="" @selected(empty($selected))>{{ $emptyOption }}</option>
    @endif
    @foreach ($options as $key => $label)
        <option
            value="{{ $key }}"
            @if ($multiple ?? false)
                @selected(in_array($key, $selected))
            @else
                @selected($key === $selected)
            @endif
        >{{ $label }}</option>
    @endforeach
</select>
