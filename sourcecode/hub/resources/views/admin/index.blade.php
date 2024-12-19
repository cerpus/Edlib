<x-layout>
    <x-slot:title>{{ trans('messages.admin-home') }}</x-slot:title>

    <ul>
        <li>
            <a href="{{ route('admin.lti-platforms.index') }}">
                {{ trans('messages.manage-lti-platforms') }}
            </a>
        </li>

        <li>
            <a href="{{ route('admin.lti-tools.index') }}">
                {{ trans('messages.manage-lti-tools') }}
            </a>
        </li>

        <li>
            <a href="{{ route('admin.contexts.index') }}">
                {{ trans('messages.manage-contexts') }}
            </a>
        </li>

        <li>
            <x-form action="{{ route('admin.rebuild-content-index') }}">
                <button class="btn btn-link p-0">{{ trans('messages.rebuild-content-index') }}</button>
            </x-form>
        </li>
    </ul>

    <h3>Admin tools</h3>

    <ul>
        @foreach ($toolExtras as $extra)
            <li>{{ $extra->tool->name }}: <a href="{{ route('content.launch-creator', [$extra->tool, $extra]) }}">{{ $extra->name }}</a></li>
        @endforeach
    </ul>

    <h3>Stats</h3>

    <div class="row">
        <div class="col-12 col-lg-6 col-xl-4">
            <table class="table">
                <tbody>
                    <tr>
                        <th scope="row">Active contents in database</th>
                        <td>{{ \App\Models\Content::count() }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Deleted contents in database</th>
                        <td>{{ \App\Models\Content::onlyTrashed()->count() }}</td>
                    </tr>
                    <tr>
                        <th>Users</th>
                        <td>{{ \App\Models\User::count() }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-layout>
