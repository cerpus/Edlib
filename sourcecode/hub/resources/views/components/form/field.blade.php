@props([
    'name' => '',
    'type' => 'text',
    'label' => '',
    'required' => false,
    'value' => '',
    'autocomplete' => null,
    'text' => null,
])

<div class="mb-3 {{ $errors->has($name) ? 'has-validation' : '' }}">
    <label for="{{ $name }}">
        {{ $label ?: $name }}
        @if ($required)
            <small class="text-secondary text-lowercase" aria-hidden="true" role="presentation">
                ({{ trans('messages.required') }})
            </small>
        @endif
    </label>

    <x-form.input
        :name="$name"
        :type="$type"
        :aria-describedby="$errors->has($name) ? 'errors-' . $name : null"
        :required="$required"
        :value="$value"
        :autocomplete="$autocomplete"
    />

    @if ($text)
        <div class="form-text">
            {{ $text }}
        </div>
    @endif

    @foreach ($errors->get($name) as $error)
        <div id="errors-{{ $name }}" class="invalid-feedback">
            <div>{{ $error }}</div>
        </div>
    @endforeach
</div>
