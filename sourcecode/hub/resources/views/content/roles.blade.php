<x-layout no-header>
    <x-slot:title>{{ trans('messages.roles') }} &mdash; {{ $content->getTitle() }}</x-slot:title>

    <x-content.details.header current="roles" :version="$content->latestVersion" />

    <h3>{{ trans('messages.users') }}</h3>

    @if (count($content->users) > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>{{ trans('messages.name') }}</th>
                    <th>{{ trans('messages.role') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($content->users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ match ($user->pivot->role) {
                            \App\Enums\ContentRole::Owner => trans('messages.owner'),
                            \App\Enums\ContentRole::Editor => trans('messages.editor'),
                            \App\Enums\ContentRole::Reader => trans('messages.reader'),
                        } }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>{{ trans('messages.nothing-to-display') }}</p>
    @endif

    <h3>{{ trans('messages.contexts') }}</h3>

    @if (count($content->contexts) > 0)
        <table class="table content-contexts">
            <thead>
                <tr>
                    <th>{{ trans('messages.context') }}</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @foreach ($content->contexts as $context)
                    <tr>
                        <td>{{ $context->name }}</td>
                        <td>
                            <x-form action="{{ route('content.remove-context', [$content, $context]) }}" method="DELETE">
                                <x-form.button class="btn-sm btn-danger">{{ trans('messages.remove') }}</x-form.button>
                            </x-form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>{{ trans('messages.nothing-to-display') }}</p>
    @endif

    @can('manage-roles', [$content])
        @if (count($available_contexts) > 0)
            <x-form action="{{ route('content.add-context', [$content]) }}">
                <x-form.field
                    name="context"
                    type="select"
                    emptyOption
                    required
                    :label="trans('messages.context')"
                    :options="$available_contexts"
                />

                <x-form.button class="btn-outline-primary">{{ trans('messages.add') }}</x-form.button>
            </x-form>
        @else
            <p>{{ trans('messages.no-available-contexts-to-add') }}</p>
        @endif
    @endcan
</x-layout>
