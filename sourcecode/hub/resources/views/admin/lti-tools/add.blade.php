<x-layout>
    <x-slot:title>Add LTI tool</x-slot:title>

    <x-admin.lti-tools.form :action="route('admin.lti-tools.store')">
        <x-slot:button>
            <x-form.button class="btn-primary">{{ trans('messages.add') }}</x-form.button>
        </x-slot:button>
    </x-admin.lti-tools.form>
</x-layout>
