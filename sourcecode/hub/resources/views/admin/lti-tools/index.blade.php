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
                @can('remove', $tool)
                    <x-form :action="route('admin.lti-tools.remove', [$tool])" method="DELETE">
                        <x-form.button class="btn-sm btn-danger">
                            {{ trans('messages.remove') }}
                        </x-form.button>
                    </x-form>
                @endcan
            </li>
        @endforeach
    </ul>

    <p><a href="{{ route('admin.lti-tools.add') }}" class="btn btn-outline-primary">Add LTI tool</a></p>
</x-layout>
