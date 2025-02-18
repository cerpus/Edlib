<div class="modal preview-modal">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-lg-down modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h4 class="modal-title">{{ $version->getTitle() }}</h4>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="{{trans('messages.close')}}"
                ></button>
            </div>

            <div class="modal-body">
                <x-lti-launch :launch="$launch" class="w-100 border" />
            </div>

            <div class="modal-footer border-0">
                <div class="flex-fill">
                    <div>
                        <strong>{{ trans('messages.edited') }}:</strong>
                        <time
                            datetime="{{ $version->created_at->toIso8601String() }}"
                            data-dh-relative="true"
                        ></time>
                    </div>
                </div>

                @if ($version->published)
                    <a
                        href="{{ route('content.share', [$content]) }}"
                        class="btn btn-secondary d-flex gap-2"
                        hx-get="{{ route('content.share-dialog', [$content]) }}"
                        hx-target="#modal-container"
                        hx-swap="beforeend"
                        data-modal="true"
                    >
                        <x-icon name="share" />
                        {{ trans('messages.share') }}
                    </a>
                @endif

                @can('edit', [$content])
                    <a href="{{ route('content.edit', [$content, $version]) }}" class="btn btn-secondary" role="button">
                        {{ trans('messages.edit-content') }}
                    </a>
                @endcan

                @can('use', [$content, $version])
                    <x-form action="{{ $version->getUseUrl() }}" method="POST">
                        <button class="btn btn-primary use-button" role="button">
                            {{ trans('messages.use-content') }}
                        </button>
                    </x-form>
                @endcan
            </div>
        </div>
    </div>
</div>
