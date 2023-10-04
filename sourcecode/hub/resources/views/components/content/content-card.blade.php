{{-- ToDo: Remove these when actual values are available --}}
@php($type = ['NDLA Virtual Tour (360)', 'Image Pair', 'Course Presentation', 'Audio', 'Interactive video'][mt_rand(0, 4)])
@php($lang = ['ENG', 'NOB', 'NNO', 'SWE'][mt_rand(0, 3)])
@php($views = [0, 7, 11, 58, 452, 9032, 69111, 912731, 5581751][mt_rand(0, 8)])
{{-- End --}}

@php($showDrafts ??= false)
@php($version = $showDrafts ? $content->latestVersion : $content->latestPublishedVersion)

<article class="card card-grid shadow-sm">
    <div class="card-header border-bottom-0 position-relative">
        <a
            href="{{ route('content.preview', [$content->id]) }}"
            class="text-decoration-none link-body-emphasis"
            aria-label="{{ trans('messages.preview') }}"
        >
            {{-- TODO: Date and time should be displayed in users timezone --}}
            <div class="card-header-updated text-truncate d-none d-md-block" title="{{$content->updated_at->isoFormat('LLLL')}}">
                {{ trans('messages.edited') }}:
                {{
                    $content->updated_at->isToday() ? ucfirst(trans('messages.today')) . $content->updated_at->isoFormat(' LT') :
                    ($content->updated_at->isSameAs('W', \Illuminate\Support\Carbon::now()) ? ucfirst($content->updated_at->isoFormat('dddd LT')) : $content->updated_at->isoFormat('LL'))
                }}
            </div>
            <h6 class="text-line-clamp clamp-2-lines fw-bold" aria-label="{{ trans('messages.title') }}">
                {{ $version->resource->title }}
            </h6>
        </a>
        <div class="badge position-absolute end-0 top-100 card-preview-badge d-none d-md-inline-block">
            <x-icon name="eye"/>
            <span title="{{ trans('messages.number-of-views') }}">{{ $views }}</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row card-text mb-2">
            <div class="col-auto small" aria-label="{{ trans('messages.content-type') }}">
                {{ $type }}
            </div>
            <div class="col-auto badge text-bg-primary" aria-label="{{ trans('messages.language') }}">
                {{ $lang }}
            </div>
        </div>
        <div class="card-text small" aria-label="{{ trans('messages.author') }}">
            @foreach ($content->users as $user)
                {{ $user->name }}
            @endforeach
        </div>
    </div>
    <div class="card-footer d-flex align-items-center border-0">
        <x-content.action-buttons btnClass="btn-sm" :$content :$lti />
        <div class="badge position-absolute end-0 d-md-none card-preview-badge">
            <x-icon name="eye"/>
            <div title="{{ trans('messages.number-of-views') }}">{{ $views }}</div>
        </div>
    </div>
</article>
