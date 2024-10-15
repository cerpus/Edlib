<label {{ $attributes
    ->except(['required'])
    ->merge([
        'class' => 'form-label',
    ]) }}
>
    {{ $slot }}

    @if ($required ?? false)
        <small class="text-secondary text-lowercase" aria-hidden="true" role="presentation">
            ({{ trans('messages.required') }})
        </small>
    @endif
</label>
