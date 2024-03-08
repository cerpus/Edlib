@props(['content', 'showDrafts' => false, 'titlePreviews' => false])

{{-- ToDo: Remove these when actual values are available --}}
@php($type = ['NDLA Virtual Tour (360)', 'Image Pair', 'Course Presentation', 'Audio', 'Interactive video'][mt_rand(0, 4)])
{{-- End --}}

@php($version = $showDrafts ? $content->latestVersion : $content->latestPublishedVersion)

<article class="card content-card">
    <header class="card-header content-card-header border-bottom-0 fw-bold position-relative">
        <a
            @if ($version->published)
                href="{{ route('content.details', [$content]) }}"
            @else
                href="{{ route('content.version-details', [$content, $version]) }}"
            @endif
            class="text-decoration-none link-body-emphasis"
            @if ($titlePreviews)
                hx-get="{{ route('content.preview', [$content, $version]) }}"
                hx-target="#previewModal"
                data-bs-toggle="modal"
                data-bs-target="#previewModal"
            @endif
        >
            <div class="content-card-header-updated text-truncate d-none d-md-block fw-normal">
                {{ trans('messages.edited') }}:
                <time
                    datetime="{{$content->updated_at->toIso8601String()}}"
                    data-dh-relative="true"
                ></time>
            </div>
            <div class="text-line-clamp clamp-2-lines content-card-title">
                {{ $version->title }}
            </div>
        </a>
        @if(!$version->published)
            <div class="badge text-bg-primary position-absolute end-0 top-0 d-none d-md-inline-block">
                {{ trans('messages.draft') }}
            </div>
        @endif
        <div class="badge position-absolute end-0 top-100 content-card-preview-badge d-none d-md-inline-block">
            <x-icon name="eye"/>
            <span class="content-card-views" title="{{ trans('messages.number-of-views') }}">
                {{ $content->views_count }}
            </span>
        </div>
    </header>
    <div class="card-body">
        <div class="row card-text mb-2">
            <div class="col-auto small">
                {{ $version->getDisplayedContentType() }}
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
    <div class="card-footer d-flex align-items-center bg-transparent border-0 action-buttons">
        <x-content.action-buttons :$content :$version :show-preview="!$titlePreviews" />
        <div class="badge position-absolute end-0 d-md-none content-card-preview-badge">
            <x-icon name="eye"/>
            <div class="content-card-views" title="{{ trans('messages.number-of-views') }}">
                {{ $content->views_count }}
            </div>
        </div>
    </div>
</article>
