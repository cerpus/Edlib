<x-layout>
    <x-slot:title>{{ trans('messages.manage-lti-platforms') }}</x-slot:title>

    <p class="alert alert-info">{{ trans('messages.lti-platforms-description', ['site' => config('app.name')]) }}</p>

    @if ($createdPlatform)
        <div class="bg-success-subtle border border-success border-1 p-3 pb-0 mb-3 lti-platform-added-alert" role="alert">
            <p>{{ trans('messages.lti-platform-created-success', ['name' => $createdPlatform->name]) }}</p>
            <p>
                {{ trans('messages.key') }}: <kbd class="user-select-all lti-platform-added-alert-key">{{ $createdPlatform->key }}</kbd><br>
                {{ trans('messages.secret') }}: <kbd class="user-select-all lti-platform-added-alert-secret">{{ $createdPlatform->secret }}</kbd>
            <p>
            <p>{{ trans('messages.lti-platform-secret-shown-only-once') }}</p>
        </div>
    @endif

    <h2 class="fs-4">{{ trans('messages.add-lti-platform') }}</h2>

    <x-admin.lti-platforms.form action="{{ route('admin.lti-platforms.store') }}" method="POST">
        <x-slot:button>
            <x-form.button class="btn-primary">
                {{ trans('messages.create') }}
            </x-form.button>
        </x-slot:button>
    </x-admin.lti-platforms.form>

    @if (count($platforms) > 0)
        <hr>
        <ul class="row list-unstyled">
            @foreach ($platforms as $platform)
                <li class="col-12 col-md-6 col-lg-4 mb-3">
                    <div class="card lti-platform-card">
                        <div class="card-header">
                            <h5 class="card-title lti-platform-card-title">{{ $platform->name }}</h5>
                        </div>
                        <div class="card-body">
                            <dl>
                                <dt>{{ trans('messages.key') }}</dt>
                                <dd><kbd class="user-select-all">{{ $platform->key }}</kbd></dd>
                                <dt>{{ trans('messages.created') }}</dt>
                                <dd>{{ $platform->created_at }}</dd>
                                <dt>{{ trans('messages.single-sign-on') }}</dt>
                                <dd class="lti-platform-card-enable-sso">{{ $platform->enable_sso ? trans('messages.yes') : trans('messages.no') }}</dd>
                                <dt>{{ trans('messages.lti-platform-authorizes-edit') }}</dt>
                                <dd class="lti-platform-card-authorizes-edit">{{ $platform->authorizes_edit ? trans('messages.yes') : trans('messages.no') }}</dd>
                                <dt><a href="{{ route('admin.lti-platforms.contexts', [$platform]) }}">{{ trans('messages.contexts') }}</a></dt>
                                <dd class="lti-platform-card-context-count">{{ count($platform->contexts) }}</dd>
                            </dl>
                        </div>

                        @can('delete', $platform)
                            <div class="card-footer d-flex gap-2">
                                <a
                                    href="{{ route('admin.lti-platforms.edit', [$platform]) }}"
                                    class="btn btn-outline-success"
                                >
                                    {{ trans('messages.edit') }}
                                </a>

                                <x-form
                                    action="{{ route('admin.lti-platforms.remove', [$platform]) }}"
                                    method="DELETE"
                                    hx-confirm="{{ trans('messages.confirm-lti-platform-deletion') }}"
                                    hx-delete="{{ route('admin.lti-platforms.remove', [$platform]) }}"
                                >
                                    <x-form.button class="btn btn-danger">{{ trans('messages.remove') }}</x-form.button>
                                </x-form>
                            </div>
                        @endcan
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    {{ $platforms->links() }}
</x-layout>
