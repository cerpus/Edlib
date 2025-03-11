<x-layout no-header no-nav no-footer>
    <x-slot:title>{{ $content->title }}</x-slot:title>

    <x-lti-launch :launch="$launch" class="w-100" />

    <nav class="d-flex gap-3 my-3">
        <span class="flex-grow-1" role="separator"></span>

        <a href="{{ $content->getDetailsUrl() }}" class="btn btn-primary">
            {{ trans('messages.more-details') }}
        </a>
    </nav>
</x-layout>
