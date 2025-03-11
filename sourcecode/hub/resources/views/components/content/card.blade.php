@props(['content'])

<article class="card content-card">
    <header class="card-header content-card-header border-bottom-0 fw-bold position-relative">
        <a
            href="{{ $content->detailsUrl }}"
            class="text-decoration-none link-body-emphasis"
            hx-get="{{ $content->previewUrl }}"
            hx-target="#modal-container"
            hx-swap="beforeend"
            data-modal="true"
        >
            <div class="content-card-header-updated text-truncate d-none d-md-block fw-normal">
                {{ trans('messages.edited') }}:
                <time
                    datetime="{{ $content->createdAt?->toIso8601String() }}"
                    data-dh-relative="true"
                ></time>
            </div>
            <div class="text-line-clamp clamp-2-lines content-card-title">
                {{ $content->title }}
            </div>
        </a>
        @if(!$content->isPublished)
            <div class="badge text-bg-primary position-absolute end-0 top-0 d-none d-md-inline-block">
                {{ trans('messages.draft') }}
            </div>
        @endif
        <div class="badge position-absolute end-0 top-100 content-card-preview-badge d-none d-md-inline-block">
            <x-icon name="eye"/>
            <span class="content-card-views" title="{{ trans('messages.number-of-views') }}">
                {{ $content->viewsCount }}
            </span>
        </div>
    </header>
    <div class="card-body overflow-hidden">
        <div class="row card-text mb-2">
            <div class="col-auto small content-type">
                {{ $content->contentType }}
            </div>
            <div
                class="col-auto badge text-bg-primary fw-normal"
                @isset($content->languageDisplayName))
                    title="{{$content->languageDisplayName}}"
                @endisset
            >
                {{ $content->languageIso639_3 }}
            </div>
        </div>
        <div class="card-text small text-line-clamp clamp-2-lines">
            {{ $content->users }}
        </div>
    </div>
    <div class="card-footer d-flex align-items-center bg-transparent border-0 action-buttons">
        <x-content.action-buttons :$content />
        <div class="badge position-absolute end-0 d-md-none content-card-preview-badge">
            <x-icon name="eye"/>
            <div class="content-card-views" title="{{ trans('messages.number-of-views') }}">
                {{ $content->viewsCount }}
            </div>
        </div>
    </div>
</article>
