<div class="col-md-3 col-lg-3 d-md-block d-none">
    <select class="form-select" id="languageDropdown">
        @foreach (['Language1', 'Language2', 'Language3', 'Language4', 'Language5'] as $label)
            <option value="{{ $label }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-4 col-lg-3 d-md-block d-none">
    <select class="form-select" id="lastChangedDropdown">
        @foreach (['Last Changed 1', 'Last Changed 2', 'Last Changed 3', 'Last Changed 4', 'Last Changed 5'] as $label)
            <option value="{{ $label }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
