@props(['id'])

<span {{ $attributes->merge(['class' => "spinner-container position-absolute"]) }} >
    <span class="spinner-border unhide-when-loading" role="status" id="{{$id}}">
        <span class="visually-hidden">{{ trans('messages.loading') }}</span>
    </span>
</span>
