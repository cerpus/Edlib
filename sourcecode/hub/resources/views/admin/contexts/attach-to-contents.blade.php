<x-layout>
    <x-slot:title>{{ trans('messages.attach-context-to-contents') }}</x-slot:title>

    <p class="alert alert-danger">{{ trans('messages.attach-context-to-contents-warning') }}</p>

    <x-form action="" method="POST">
        <x-form.field
            name="context"
            type="select"
            emptyOption
            required
            :label="trans('messages.context')"
            :options="$available_contexts"
        />

        <x-form.button class="btn btn-primary">{{ trans('messages.start-job') }}</x-form.button>
    </x-form>
</x-layout>
