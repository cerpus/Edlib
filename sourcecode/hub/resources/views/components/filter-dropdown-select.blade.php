@props(['id', 'ariaLabel', 'options'])

<select class="form-select" id="{{ $id }}" aria-label="{{ $ariaLabel }}">
    @foreach ($options as $label)
        <option value="{{ $label }}">{{ $label }}</option>
    @endforeach
</select>
