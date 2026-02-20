<x-layout>
    <x-slot:title>{{ trans('messages.admin-home') }}</x-slot:title>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-bg-light h-100">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold">{{ \App\Models\Content::count() }}</div>
                    <div class="text-body-secondary">Active contents</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-bg-light h-100">
                <div class="card-body text-center">
                    @php($deletedCount = \App\Models\Content::onlyTrashed()->count())
                    <div class="fs-2 fw-bold">
                        @if($deletedCount > 0)
                            <a href="{{ route('admin.content.deleted') }}" class="text-decoration-none">{{ $deletedCount }}</a>
                        @else
                            0
                        @endif
                    </div>
                    <div class="text-body-secondary">Deleted contents</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-bg-light h-100">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold">{{ \App\Models\User::count() }}</div>
                    <div class="text-body-secondary">Users</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-bg-light h-100">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold">{{ \App\Models\ContentLock::active()->count() }}</div>
                    <div class="text-body-secondary">{{ trans('messages.active-content-locks') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h2 class="card-title fs-5 mb-0">Management</h2>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.lti-platforms.index') }}" class="list-group-item list-group-item-action d-flex align-items-center">
                        <x-icon name="hdd-network" class="me-2" />
                        {{ trans('messages.manage-lti-platforms') }}
                    </a>
                    <a href="{{ route('admin.lti-tools.index') }}" class="list-group-item list-group-item-action d-flex align-items-center">
                        <x-icon name="tools" class="me-2" />
                        {{ trans('messages.manage-lti-tools') }}
                    </a>
                    <a href="{{ route('admin.contexts.index') }}" class="list-group-item list-group-item-action d-flex align-items-center">
                        <x-icon name="diagram-3" class="me-2" />
                        {{ trans('messages.manage-contexts') }}
                    </a>
                    <a href="{{ route('admin.attach-context-to-contents') }}" class="list-group-item list-group-item-action d-flex align-items-center">
                        <x-icon name="link-45deg" class="me-2" />
                        {{ trans('messages.attach-context-to-contents') }}
                    </a>
                    <a href="{{ route('admin.admins.index') }}" class="list-group-item list-group-item-action d-flex align-items-center">
                        <x-icon name="person-fill-gear" class="me-2" />
                        {{ trans('messages.admins') }}
                    </a>
                    <a href="{{ route('admin.content-exclusions.index') }}" class="list-group-item list-group-item-action d-flex align-items-center">
                        <x-icon name="slash-circle" class="me-2" />
                        Content exclusions
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 d-flex flex-column gap-3">
            @if($toolExtras->isNotEmpty())
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title fs-5 mb-0">Admin tools</h2>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach ($toolExtras as $extra)
                            <a href="{{ route('content.launch-creator', [$extra->tool, $extra]) }}" class="list-group-item list-group-item-action d-flex align-items-center">
                                <x-icon name="wrench" class="me-2" />
                                <span>{{ $extra->name }} <span class="text-body-secondary">&mdash; {{ $extra->tool->name }}</span></span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="card border-danger">
                <div class="card-header text-bg-danger">
                    <h2 class="card-title fs-5 mb-0">{{ trans('messages.danger-zone') }}</h2>
                </div>
                <div class="card-body">
                    <x-form
                        action="{{ route('admin.rebuild-content-index') }}"
                        hx-post="{{ route('admin.rebuild-content-index') }}"
                        hx-confirm="{{ trans('messages.confirm-reindex') }}"
                    >
                        <button class="btn btn-outline-danger">
                            <x-icon name="arrow-repeat" class="me-1" />
                            {{ trans('messages.rebuild-content-index') }}
                        </button>
                    </x-form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
