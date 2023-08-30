<x-layout>
    <x-slot:title>{{ trans('messages.add-an-lti-resource') }}</x-slot:title>

    <p class="alert alert-warning">LTI 1.3, LTI 2.0, and any newer versions are currently not supported.</p>

    <x-form>
        <x-form.field
            name="title"
            :label="trans('messages.title')"
        />

        <x-form.field
            name="launch_url"
            :label="trans('messages.launch-url')"
        />

        <x-form.field
            name="consumer_key"
            :label="trans('messages.key')"
        />

        <x-form.field
            name="consumer_secret"
            type="password"
            :label="trans('messages.secret')"
        />

        <x-form.button class="btn-primary">Add</x-form.button>
    </x-form>
</x-layout>
