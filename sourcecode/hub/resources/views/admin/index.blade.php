<x-layout>
    <x-slot:title>{{ trans('messages.admin-home') }}</x-slot:title>

    <ul>
        <li>
            <a href="{{ route('admin.lti-tools.index') }}">
                {{ trans('messages.manage-lti-tools') }}
            </a>
        </li>
    </ul>
</x-layout>
