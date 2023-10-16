@php
    $languageOptions = ['Language1', 'Language2', 'Language3', 'Language4', 'Language5'];
    $lastChangedOptions = ['Last Changed 1', 'Last Changed 2', 'Last Changed 3', 'Last Changed 4', 'Last Changed 5'];
@endphp

<div class="col-md-3 col-lg-3 d-md-block d-none">
    <x-filter-dropdown-select id="languageDropdown" ariaLabel="{{ trans('messages.language') }}">
        @foreach ($languageOptions as $label)
            <option value="{{ $label }}">{{ $label }}</option>
        @endforeach
    </x-filter-dropdown-select>
</div>

<div class="col-md-4 col-lg-3 d-md-block d-none">
    <x-filter-dropdown-select id="lastChangedDropdown" ariaLabel="{{ trans('messages.last-changed') }}">
        @foreach ($lastChangedOptions as $label)
            <option value="{{ $label }}">{{ $label }}</option>
        @endforeach
    </x-filter-dropdown-select>
</div>
