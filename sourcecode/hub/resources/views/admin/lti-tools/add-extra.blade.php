<x-layout>
    <x-slot:title>{{ trans('messages.add-extra-endpoint-for', ['tool' => $tool->name]) }}</x-slot:title>

    <x-form :action="route('admin.lti-tools.store-extra', [$tool])" method="POST">
        <x-form.field name="name" :label="trans('messages.name')" />

        <x-form.field name="slug" :label="trans('messages.url-slug')" />

        <x-form.field name="lti_launch_url" :label="trans('messages.lti-launch-url')" />

        <div class="form-check mb-3">
            <x-form.checkbox name="admin" />
            <label class="form-check-label" for="admin">{{ trans('messages.admin-tool') }}</label>
        </div>

        <x-form.button class="btn-success">{{ trans('messages.add') }}</x-form.button>
    </x-form>
</x-layout>
