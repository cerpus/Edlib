@props(['content'])

<article class="card content-list-item shadow-sm">
    <div class="card-body">
        <div class="row">
            <a
                href="{{ $content->detailsUrl }}"
                class="col text-decoration-none link-body-emphasis"
                hx-get="{{ $content->previewUrl }}"
                hx-target="#modal-container"
                hx-swap="beforeend"
                data-modal="true"
            >
                <h5 class="text-line-clamp clamp-3-lines fw-bold" aria-label="{{ trans('messages.title') }}">
                    {{ $content->title }}
                </h5>
            </a>
            <time class="col-2" aria-label="{{ trans('messages.last-changed') }}" datetime="{{ $content->createdAt?->toIso8601String() }}"></time>
            <div class="col-2" aria-label="{{ trans('messages.author') }}">
                {{ $content->users }}
            </div>
            <div class="col-2" aria-label="{{ trans('messages.language') }}">
                {{ $content->languageDisplayName ?: $content->languageIso639_3 }}
            </div>
            <div class="col-2" aria-label="{{ trans('messages.views') }}">
                {{ $content->viewsCount }}
                @if(!$content->isPublished)
                    <div class="badge text-bg-primary position-absolute end-0 top-0 d-none d-md-inline-block">
                        {{ trans('messages.draft') }}
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col" aria-label="{{ trans('messages.content-type') }}">
                {{ $content->contentType }}
            </div>
        </div>
    </div>
    <div class="card-footer d-flex align-items-center justify-content-end border-0 action-buttons">
        <x-content.action-buttons :$content />
    </div>
</article>
