<x-layout current="create">
    <x-slot:title>{{ trans('messages.create-content') }}</x-slot:title>

    <p>{{ trans('messages.select-a-content-type') }}</p>

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

    @can('admin')
        <p>
            <a href="{{ route('admin.lti-tools.index') }}" class="btn btn-outline-secondary">
                {{ trans('messages.manage-lti-tools') }}
            </a>
        </p>
    @endcan
</x-layout>
