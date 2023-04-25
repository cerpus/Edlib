<div class="mb-3 {{ $errors->has($name) ? 'has-validation' : '' }}">
    <label for="{{ $name }}">
        {{ $label ?? $name }}
        @if ($required ?? false)
            <small class="text-secondary text-lowercase" aria-hidden="true" role="presentation">
                ({{ trans('messages.required') }})
            </small>
        @endif
    </label>

    @switch($type ?? 'text')
        @case('text')
        @case('password')
        @case('search')
        @case('email')
        @default
            <x-form.input
                name="{{ $name }}"
                type="{{ $type ?? 'text' }}"
                :aria-describedby="$errors->has($name) ? 'errors-'.$name : null"
                :required="$required ?? false"
            />
            @break
    @endswitch

    @if (isset($text))
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
