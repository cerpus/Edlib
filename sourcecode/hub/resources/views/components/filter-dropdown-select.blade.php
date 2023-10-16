@props(['id', 'ariaLabel'])

<select class="form-select" id="{{ $id }}" aria-label="{{ $ariaLabel }}">
    {{ $slot }}
</select>
