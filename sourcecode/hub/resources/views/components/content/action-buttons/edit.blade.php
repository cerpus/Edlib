@props(['url', 'lockedByUserName' => null])
<a
    href="{{ $url }}"
    class="btn btn-secondary btn-sm me-1 content-edit-button {{ $lockedByUserName ? 'disabled cursor-not-allowed' : '' }}"
    @if($lockedByUserName)
    disabled="disabled"
    title="{{ trans('messages.the-lock-is-held-by', ['name' => $lockedByUserName]) }}"
    @endif
>
    @if($lockedByUserName)
        <x-icon name="lock" />
    @endif
    {{ trans('messages.edit-content') }}
</a>
