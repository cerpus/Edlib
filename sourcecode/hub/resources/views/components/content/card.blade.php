{{-- ToDo: Remove these when actual values are available --}}
@php($type = ['NDLA Virtual Tour (360)', 'Image Pair', 'Course Presentation', 'Audio', 'Interactive video'][mt_rand(0, 4)])
{{-- End --}}

@php($showDrafts ??= false)
@php($version = $showDrafts ? $content->latestVersion : $content->latestPublishedVersion)

<article class="card content-card">
    <header class="card-header content-card-header border-bottom-0 fw-bold position-relative">
        <a
            href="{{ route('content.details', [$content->id]) }}"
            class="text-decoration-none link-body-emphasis"
            aria-label="{{ trans('messages.preview') }}"
        >
            {{-- TODO: Date and time should be displayed in users timezone --}}
            <div class="content-card-header-updated text-truncate d-none d-md-block fw-normal" title="{{$content->updated_at->isoFormat('LLLL')}}">
                {{ trans('messages.edited') }}:
                {{
                    $content->updated_at->isToday() ? ucfirst(trans('messages.today')) . $content->updated_at->isoFormat(' LT') :
                    ($content->updated_at->isSameAs('W', \Illuminate\Support\Carbon::now()) ? ucfirst($content->updated_at->isoFormat('dddd LT')) : $content->updated_at->isoFormat('LL'))
                }}
            </div>
            <div class="text-line-clamp-2 content-card-title">
                {{ $version->title }}
            </div>
        </a>
        <div class="badge position-absolute end-0 top-100 content-card-preview-badge d-none d-md-inline-block">
            <x-icon name="eye"/>
            <span class="content-card-views" title="{{ trans('messages.views') }}">
                {{ $content->views_count }}
            </span>
        </div>
    </header>
    <div class="card-body">
        <div class="row card-text mb-2">
            <div class="col-auto small">
                {{ $type }}
            </div>
            <div class="col-auto badge text-bg-primary">
                {{ strtoupper($version->language_iso_639_3) }}
            </div>
        </div>
        <div class="card-text small">
            @foreach ($content->users as $user)
                {{ $user->name }}
            @endforeach
        </div>
    </div>
    <div class="card-footer content-card-footer d-flex align-items-center bg-transparent border-0">
        @can('use', $content)
            <x-form action="{{ route('content.use', [$content]) }}" method="POST">
                <button class="btn btn-primary btn-sm me-1 content-card-use-button">
                    {{ trans('messages.use-content') }}
                </button>
            </x-form>
        @endcan
        @can('edit', $content)
            <a
                href="{{ route('content.edit', [$content]) }}"
                class="btn btn-secondary btn-sm d-none d-md-inline-block me-1 content-card-edit-link"
            >
                {{ trans('messages.edit-content') }}
            </a>
        @endcan
        @canany(['view', 'edit', 'delete'], $content)
            <div class="dropup">
                <button
                    type="button"
                    class="btn btn-sm btn-secondary border-0 dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="{{ trans('messages.toggle-menu') }}"
                >
                    <x-icon name="three-dots-vertical" />
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @can('view', $content)
                        <li>
                            <a href="{{ route('content.details', [$content->id]) }}" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#previewModal">
                                <x-icon name="info-lg" class="me-2" />
                                {{ trans('messages.preview') }}
                            </a>
                        </li>
                    @endcan
                    @can('edit', $content)
                        <li class="d-md-none">
                            <a href="{{ route('content.edit', [$content->id]) }}" class="dropdown-item">
                                <x-icon name="pencil" class="me-2" />
                                {{ trans('messages.edit-content') }}
                            </a>
                        </li>
                    @endcan
                    @can('delete', $content)
                        <li>
                            <a href="#" class="btn btn-primary dropdown-item"  data-bs-toggle="modal" data-bs-target="#deletionModal">
                                <x-icon name="x-lg" class="me-2 text-danger" />
                                {{ trans('messages.delete-content') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        @endcan
        <div class="badge position-absolute end-0 d-md-none content-card-preview-badge">
            <x-icon name="eye"/>
            <div class="content-card-views" title="{{ trans('messages.views') }}">
                {{ $content->views_count }}
            </div>
        </div>
    </div>
</article>
