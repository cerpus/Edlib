@props(['name', 'options', 'selected', 'emptyOption' => false])
<select {{ $attributes
    ->class([
        "form-select"
    ])
    ->merge([
        'name' => $name,
    ])
}}>
    @if($emptyOption)
        <option value="" @selected(empty($selected))>{{ $emptyOption }}</option>
    @endif
    @foreach ($options as $key => $label)
        <option value="{{ $key }}" @selected($key === $selected)>{{ $label }}</option>
    @endforeach
</select>
