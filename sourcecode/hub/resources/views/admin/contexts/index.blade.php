<x-layout>
    <x-slot:title>Contexts</x-slot:title>

    <p class="alert alert-info">{{ trans('messages.context-help') }}</p>

    @if (count($contexts) > 0)
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ trans('messages.name') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contexts as $context)
                    <tr>
                        <td><kbd>{{ $context->name }}</kbd></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>{{ trans('messages.nothing-to-display') }}
    @endif

    <hr>

    <x-form action="{{ route('admin.contexts.add') }}">
        <x-form.field
            name="name"
            type="text"
            :label="trans('messages.name')"
            :text="trans('messages.context-name-help')"
        />

        <x-form.button class="btn-primary">{{ trans('messages.add') }}</x-form.button>
    </x-form>
</x-layout>
