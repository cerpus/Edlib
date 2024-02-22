@props(['content', 'showDrafts' => false, 'titlePreviews' => false])
{{-- ToDo: Remove these when actual values are available --}}
@php($type = ['NDLA Virtual Tour (360)', 'Image Pair', 'Course Presentation', 'Audio', 'Interactive video'][mt_rand(0, 4)])
{{-- End --}}

@php($version = $showDrafts ? $content->latestVersion : $content->latestPublishedVersion)

<article class="card content-list-item shadow-sm">
    <div class="card-body">
        <div class="row">
            <a
                href="{{ route('content.details', [$content->id]) }}"
                class="col text-decoration-none link-body-emphasis"
                @if ($titlePreviews)
                    data-bs-toggle="modal"
                    data-bs-target="#previewModal"
                    data-content-preview-url="{{ route('content.preview', [$content, $version]) }}"
                    data-content-share-url="{{ route('content.share', [$content, SessionScope::TOKEN_PARAM => null]) }}"
                    data-content-title="{{ $version->title }}"
                    data-content-created="{{ $content->created_at->isoFormat('LLLL') }}"
                    data-content-updated="{{ $content->updated_at->isoFormat('LLLL') }}"
                    @can('use', $content) data-content-use-url="{{ route('content.use', [$content]) }}" @endif
                    @can('edit', $content) data-content-edit-url="{{ route('content.edit', [$content]) }}" @endif
                @endif
            >
                <h5 class="text-line-clamp clamp-3-lines fw-bold" aria-label="{{ trans('messages.title') }}">
                    {{ $version->title }}
                </h5>
            </a>
            {{-- TODO: Date and time should be displayed in users timezone --}}
            <div class="col-2" title="{{$content->updated_at->isoFormat('LLLL')}}" aria-label="{{ trans('messages.last-changed') }}">
                {{ $content->updated_at->isoFormat('L') }}
            </div>
            <div class="col-2" aria-label="{{ trans('messages.author') }}">
                @foreach ($content->users as $user)
                    {{ $user->name }}
                @endforeach
            </div>
            <div class="col-2" aria-label="{{ trans('messages.language') }}">
                {{ $version->language_iso_639_3 }}
            </div>
            <div class="col-2" aria-label="{{ trans('messages.views') }}">
                {{ $content->views_count }}
                @if(!$version->published)
                    <div class="badge text-bg-primary position-absolute end-0 top-0 d-none d-md-inline-block">
                        {{ trans('messages.draft') }}
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col" aria-label="{{ trans('messages.content-type') }}">
                {{ $type }}
            </div>
        </div>
    </div>
    <div class="card-footer d-flex align-items-center justify-content-end border-0 action-buttons">
        <x-content.action-buttons :$content :$version :show-preview="!$titlePreviews" />
    </div>
</article>
