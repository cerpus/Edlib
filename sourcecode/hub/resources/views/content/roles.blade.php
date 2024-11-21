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
        <table class="table">
            <thead>
                <tr>
                    <th>{{ trans('messages.context') }}</th>
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

    @can('add-context', [$content])
        <x-form action="{{ route('content.add-context', [$content]) }}">
            <x-form.field
                name="context"
                type="select"
                :options="\App\Models\Context::all()->mapWithKeys(fn (Context $context) => [$context->id => $context->name])"
            />

            <x-form.button class="btn-outline-primary">{{ trans('messages.add') }}</x-form.button>
        </x-form>
    @endcan
</x-layout>
