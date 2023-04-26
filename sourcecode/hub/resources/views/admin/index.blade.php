<x-layout>
    <x-slot:title>{{ trans('messages.admin-home') }}</x-slot:title>

    <ul>
        <li>
            <a href="{{ route('admin.lti-tools.index') }}">
                {{ trans('messages.manage-lti-tools') }}
            </a>
        </li>

        <li>
            <x-form action="{{ route('admin.rebuild-content-index') }}">
                <button class="btn btn-link p-0">{{ trans('messages.rebuild-content-index') }}</button>
            </x-form>
        </li>
    </ul>
</x-layout>
