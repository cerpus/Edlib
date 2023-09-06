<x-layout>
    <x-slot:title>{{ trans('messages.manage-lti-platforms') }}</x-slot:title>

    <p class="alert alert-info">{{ trans('messages.lti-platforms-description') }}</p>

    @if ($createdPlatform)
        <div class="bg-success-subtle border border-success border-1 p-3 pb-0 mb-3" role="alert">
            <p>{{ trans('messages.lti-platform-created-success', ['name' => $createdPlatform->name]) }}</p>
            <p>
                {{ trans('messages.key') }}: <kbd class="user-select-all">{{ $createdPlatform->key }}</kbd><br>
                {{ trans('messages.secret') }}: <kbd class="user-select-all">{{ $createdPlatform->secret }}</kbd>
            <p>
            <p>{{ trans('messages.lti-platform-secret-shown-only-once') }}</p>
        </div>
    @endif

    <x-form action="{{ route('admin.lti-platforms.store') }}">
        <x-form.field name="name" type="text" :label="trans('messages.name')" />

        <x-form.button class="btn-primary">
            {{ trans('messages.create') }}
        </x-form.button>
    </x-form>

    @if (count($platforms) > 0)
        <hr>
        <ul>
            @foreach ($platforms as $platform)
                <li>
                    <dl>
                        <dt>{{ trans('messages.name') }}</dt>
                        <dd>{{ $platform->name }}</dd>
                        <dt>{{ trans('messages.key') }}</dt>
                        <dd><kbd class="user-select-all">{{ $platform->key }}</kbd></dd>
                        <dt>{{ trans('messages.created') }}</dt>
                        <dd>{{ $platform->created_at }}</dd>
                    </dl>
                </li>
            @endforeach
        </ul>
    @endif

    {{ $platforms->links() }}
</x-layout>
