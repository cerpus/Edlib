<x-layout>
    <x-slot:title>Edit LTI tool</x-slot:title>

    <x-admin.lti-tools.form
        :action="route('admin.lti-tools.update', [$tool])"
        method="PATCH"
        :tool="$tool"
    >
        <x-slot:button>
            <x-form.button class="btn-primary">{{ trans('messages.update') }}</x-form.button>
        </x-slot:button>
    </x-admin.lti-tools.form>
</x-layout>
