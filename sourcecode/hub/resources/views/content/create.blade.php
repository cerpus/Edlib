<x-layout current="create">
    <x-slot:title>Content types</x-slot:title>

    <p>Select a content type</p>
    <ul>
        @foreach ($types as $type)
            <li>
                <a href="{{ route('content.launch-creator', [$type]) }}">{{ $type->name }}</a>
                <ul>
                    @foreach ($type->extras()->forAdmins(false)->get() as $extra)
                        <li>
                            <a href="{{ route('content.launch-creator', [$type, $extra]) }}">{{ $extra->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endforeach
    </ul>
</x-layout>
