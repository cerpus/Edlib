@php use App\Support\SessionScope; @endphp
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
            {{-- TODO: Date and time should be displayed in users timezone --}}
            <div class="content-card-header-updated text-truncate d-none d-md-block fw-normal" title="{{$content->updated_at->isoFormat('LLLL')}}">
                {{ trans('messages.edited') }}:
                {{
                    $content->updated_at->isToday() ? ucfirst(trans('messages.today')) . $content->updated_at->isoFormat(' LT') :
                    ($content->updated_at->isSameAs('W', \Illuminate\Support\Carbon::now()) ? ucfirst($content->updated_at->isoFormat('dddd LT')) : $content->updated_at->isoFormat('LL'))
                }}
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
