<x:modal lg fullscreen-lg-down class="share-dialog">
    <x-slot:title>{{ trans('messages.share-the-content') }}</x-slot:title>

    <kbd class="d-block user-select-all">
        {{ route('content.share', [$content, \App\Support\SessionScope::TOKEN_PARAM => null]) }}
    </kbd>

    <x-slot:footer>
        <button
            class="btn btn-primary copy-to-clipboard"
            data-share-value="{{ route('content.share', [$content, \App\Support\SessionScope::TOKEN_PARAM => null]) }}"
            data-share-success-message="{{ trans('messages.share-copied-url-success') }}"
            data-share-success-message="{{ trans('messages.share-copied-url-failed') }}"
            type="button"
        >
            {{ trans('messages.copy') }}
        </button>
    </x-slot:footer>
</x:modal>
