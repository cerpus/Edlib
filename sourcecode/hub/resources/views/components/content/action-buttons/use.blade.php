@props(['url'])
<x-form action="{{ $url }}" method="POST">
    <button class="btn btn-primary btn-sm me-1 content-use-button">
        {{ trans('messages.use-content') }}
    </button>
</x-form>
