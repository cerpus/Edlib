<x-layout>
    <x-slot:title>{{ trans('messages.edit-lti-platform') }}</x-slot:title>

    <x-admin.lti-platforms.form
        action="{{ route('admin.lti-platforms.update', [$platform]) }}"
        method="PATCH"
        :platform="$platform"
    >
        <x-slot:button>
            <x-form.button class="btn-primary">{{ trans('messages.update') }}</x-form.button>
        </x-slot:button>
    </x-admin.lti-platforms.form>
</x-layout>
