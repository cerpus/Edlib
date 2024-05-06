@props([
    'id',
    'loadingMessage',
    'doneMessage',
])

<div class="status-container" role="status" id="{{$id}}" aria-live="polite">
    {{ $doneMessage }}
    <span class="visually-hidden">
        <span class="message-loading">
            {{ $loadingMessage }}
        </span>
        <span class="message-done">
            {{ $doneMessage }}
        </span>
    </span>
    <x-spinner class="ms-3" />
</div>
