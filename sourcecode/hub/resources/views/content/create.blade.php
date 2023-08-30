<x-layout>
    <x-slot:title>Content types</x-slot:title>

    <p>Select a content type</p>
    <ul>
        @foreach ($types as $type)
            <li><a href="{{ route('content.launch-creator', [$type->id]) }}">{{ $type->name }}</a></li>
        @endforeach
    </ul>

    <p>
        <a href="{{ route('content.add-lti-resource') }}">{{ trans('messages.add-an-lti-resource') }}</a>
    </p>
</x-layout>
