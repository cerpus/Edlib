<x-layout>
    <x-slot:title>{{ trans('messages.manage-lti-platforms') }}</x-slot:title>

    <p class="alert alert-info">{{ trans('messages.lti-platforms-description', ['site' => config('app.name')]) }}</p>

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

    <div class="alert alert-info pb-0">
        <p>{{ trans('messages.lti-platforms-common-values') }}</p>
        <p>
            {{ trans('messages.lti-platform-selection-url') }}: <kbd class="user-select-all">{{ route('lti.select', [\App\Support\SessionScope::TOKEN_PARAM => null]) }}</kbd><br>
            {{ trans('messages.lti-version') }}: <kbd>1.0</kbd> / <kbd>1.1</kbd> / <kbd>1.2</kbd><br>
        </p>
    </div>

    <x-form action="{{ route('admin.lti-platforms.store') }}">
        <x-form.field name="name" type="text" :label="trans('messages.name')" />

        <fieldset aria-describedby="dangerous-stuff">
            <p class="alert alert-danger" id="dangerous-stuff">
                {{ trans('messages.dangerous-lti-platform-settings') }}
            </p>

            <x-form.field
                name="enable_sso"
                type="checkbox"
                :label="trans('messages.lti-platform-enable-sso')"
                :text="trans('messages.lti-platform-enable-sso-help', ['site' => config('app.name')])"
            />

            <x-form.field
                name="authorizes_edit"
                type="checkbox"
                :label="trans('messages.lti-platform-authorizes-edit')"
                :text="trans('messages.lti-platform-authorizes-edit-help', ['site' => config('app.name')])"
            />
        </fieldset>

        <x-form.button class="btn-primary">
            {{ trans('messages.create') }}
        </x-form.button>
    </x-form>

    @if (count($platforms) > 0)
        <hr>
        <ul class="row list-unstyled">
            @foreach ($platforms as $platform)
                <li class="col-12 col-md-6 col-lg-4 mb-3">
                    <div class="card lti-platform">
                        <div class="card-header">
                            <h5 class="card-title">{{ $platform->name }}</h5>
                        </div>
                        <div class="card-body">
                            <dl>
                                <dt>{{ trans('messages.key') }}</dt>
                                <dd><kbd class="user-select-all">{{ $platform->key }}</kbd></dd>
                                <dt>{{ trans('messages.created') }}</dt>
                                <dd>{{ $platform->created_at }}</dd>
                                <dt>{{ trans('messages.single-sign-on') }}</dt>
                                <dd>{{ $platform->enable_sso ? trans('messages.yes') : trans('messages.no') }}</dd>
                                <dt>{{ trans('messages.lti-platform-authorizes-edit') }}</dt>
                                <dd>{{ $platform->authorizes_edit ? trans('messages.yes') : trans('messages.no') }}</dd>
                            </dl>
                        </div>

                        @can('delete', $platform)
                            <div class="card-footer">
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
