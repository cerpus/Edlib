<x-layout no-header>
    <x-slot:title>{{ trans('messages.roles') }} &mdash; {{ $content->getTitle() }}</x-slot:title>

    <x-content.details.header current="roles" :version="$content->latestVersion" />

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
        <p>{{ trans('messages.content-has-no-roles') }}</p>
    @endif
</x-layout>
