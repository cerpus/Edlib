{{-- ToDo: Remove these when actual values are available --}}
@php($type = ['NDLA Virtual Tour (360)', 'Image Pair', 'Course Presentation', 'Audio', 'Interactive video'][mt_rand(0, 4)])
@php($lang = ['English', 'Bokmål', 'Nynorsk', 'Swedish'][mt_rand(0, 3)])
@php($views = [0, 7, 11, 58, 452, 9032, 69111, 912731, 5581751][mt_rand(0, 8)])
{{-- End --}}

@php($showDrafts ??= false)
@php($version = $showDrafts ? $content->latestVersion : $content->latestPublishedVersion)

<article class="card card-list shadow-sm">
    <div class="card-body">
        <div class="row">
            <a
                href="{{ route('content.preview', [$content->id]) }}"
                class="col text-decoration-none link-body-emphasis"
                aria-label="{{ trans('messages.preview') }}"
            >
                <h5 class="text-line-clamp clamp-3-lines fw-bold" aria-label="{{ trans('messages.title') }}">
                    {{ $version->resource->title }}
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
                {{ $lang }}
            </div>
            <div class="col-2" aria-label="{{ trans('messages.views') }}">
                {{ $views }}
            </div>
        </div>
        <div class="row">
            <div class="col" aria-label="{{ trans('messages.content-type') }}">
                {{ $type }}
            </div>
        </div>
    </div>
    <div class="card-footer d-flex align-items-center justify-content-end border-0">
        <x-content.action-buttons :$content :$lti />
    </div>
</article>
