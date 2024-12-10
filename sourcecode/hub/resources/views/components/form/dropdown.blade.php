@props(['name', 'options', 'selected', 'emptyOption' => false, 'multiple' => false])
<select {{ $attributes
    ->class([
        "form-select"
    ])
    ->merge([
        'name' => $name,
        'multiple' => $multiple,
    ])
}}>
    @if($emptyOption)
        <option value="" @selected(empty($selected))>{{ $emptyOption }}</option>
    @endif
    @foreach ($options as $key => $label)
        <option
            value="{{ $key }}"
            @if($multiple)
                @selected(in_array($key, $selected))
            @else
                @selected($key === $selected)
            @endif
        >{{ $label }}</option>
    @endforeach
</select>
