@php($showDrafts ??= false)
@php($version = $showDrafts ? $content->latestVersion : $content->latestPublishedVersion)
<article class="border border-bottom-0 position-relative">
    {{-- Top row--}}
    <div class="d-flex gap-3 p-3">
        <div class="">
            <x-icon name="film" class="fs-1 text-secondary" />
        </div>

        <div class="flex-grow-1">
            <h2 class="fs-5">
                @unless ($version->published)
                    <small class="badge bg-danger rounded-pill fs-6">
                        {{ trans('messages.unpublished') }}
                    </small>
                @endunless

                <a
                    href="{{ route('content.preview', [$content->id]) }}"
                    class="text-decoration-none link-body-emphasis"
                >
                    {{ $version->resource->title }}
                </a>
            </h2>

            <p class="m-0 text-secondary d-flex flex-wrap column-gap-3">
                @foreach ($content->users as $user)
                    <span class="d-inline-block text-truncate"><x-icon name="person" />{{ $user->name }}</span>
                @endforeach
                <span class="d-inline-block text-truncate">
                    {{-- TODO: localise date string --}}
                    <x-icon name="clock" />{{ $content->updated_at->toDateString() }}
                </span>
            </p>
        </div>

        <div>
{{--            <a--}}
{{--                href="{{ route('content.edit', [$content->id]) }}"--}}
{{--                class="btn btn-outline-dark"--}}
{{--                title="{{ trans('messages.edit') }}"--}}
{{--            >--}}
{{--                <x-icon name="pencil" />--}}
{{--            </a>--}}

            @isset($lti['content_item_return_url'])
                @php($request = $content->toItemSelectionRequest())
                <form
                    action="{{ $request->getUrl() }}"
                    method="{{ $request->getMethod() }}"
                    class="d-inline"
                >
                    {!! $request->toHtmlFormInputs() !!}
                    <x-form.button class="btn-primary">Select</x-form.button>
                </form>
            @endisset

{{--            <form action="{{ route('content.copy', [$content->id]) }}" method="POST" class="d-inline">--}}
{{--                @csrf--}}
{{--                <button--}}
{{--                    class="btn btn-outline-dark d-inline"--}}
{{--                    title="{{ trans('messages.copy') }}"--}}
{{--                >--}}
{{--                    <x-icon name="clipboard" />--}}
{{--                </button>--}}
{{--            </form>--}}
        </div>
    </div>

    {{-- Bottom row --}}
    <div class="
        @if($showDrafts && !$version->published)
            bg-danger
        @else
            bg-success
        @endif
        pb-1
    " aria-hidden="true"></div>
</article>
