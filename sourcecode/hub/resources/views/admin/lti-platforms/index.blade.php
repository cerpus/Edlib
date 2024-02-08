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

        <div class="form-check mb-3">
            <label class="form-check-label">
                <x-form.checkbox name="enable_sso" aria-labelledby="enable_sso_help" />
                {{ trans('messages.lti-platform-enable-sso') }}
            </label>

            <p class="form-text" id="enable_sso_help">
                {{ trans('messages.lti-platform-enable-sso-help', ['site' => config('app.name')]) }}
                <b class="text-danger">{{ trans('messages.lti-platform-enable-sso-warning') }}</b>
            </p>
        </div>

        <x-form.button class="btn-primary">
            {{ trans('messages.create') }}
        </x-form.button>
    </x-form>

    @if (count($platforms) > 0)
        <hr>
        <ul class="row list-unstyled">
            @foreach ($platforms as $platform)
                <li class="col-12 col-md-6 col-lg-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $platform->name }}</h5>
                            <dl>
                                <dt>{{ trans('messages.key') }}</dt>
                                <dd><kbd class="user-select-all">{{ $platform->key }}</kbd></dd>
                                <dt>{{ trans('messages.created') }}</dt>
                                <dd>{{ $platform->created_at }}</dd>
                                <dt>{{ trans('messages.single-sign-on') }}</dt>
                                <dd>{{ $platform->enable_sso ? trans('messages.yes') : trans('messages.no') }}</dd>
                            </dl>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    {{ $platforms->links() }}
</x-layout>
