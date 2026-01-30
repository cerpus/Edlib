@props(['url', 'lockedByUserName' => null])
<a
    href="{{ $url }}"
    @class([
        'btn btn-secondary btn-sm me-1 content-edit-button',
        'disabled cursor-not-allowed' => $lockedByUserName,
    ])
    @disabled($lockedByUserName)
>
    @if($lockedByUserName)
        <x-icon name="lock" />
    @endif
    {{ trans('messages.edit-content') }}
</a>
