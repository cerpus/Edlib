<x-layout>
    <x-slot:title>LTI tools</x-slot:title>

    <ul>
        @foreach ($tools as $tool)
            <li>
                <strong>{{ $tool->name }}</strong>
                <dl>
                    <dt>Consumer key
                    <dd>
                        @if ($tool->consumer_key)
                            <kbd>{{ $tool->consumer_key }}</kbd>
                    @else
                        (none)
                    @endif
                    <dt>Associated resources
                    <dd>{{ $tool->resources_count }}
                </dl>
            </li>
        @endforeach
    </ul>

    <p><a href="{{ route('admin.lti-tools.add') }}">Add LTI tool</a></p>
</x-layout>
