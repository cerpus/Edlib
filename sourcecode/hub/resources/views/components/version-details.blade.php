@props(['version', 'index', 'loop'])

<li class="d-flex flex-column p-1 mb-1 rounded {{ $loop->first ? 'border border-success bg-success-subtle' : 'border border-secondary bg-white' }}">
    <div class="version-details-container d-flex w-100 align-items-center justify-content-between">
        <div class="version-details-80">
            <div class="version-number">
                <b>{{ trans('messages.version') }} {{ $index + 1 }}</b>
            </div>
            <div class="version-date">
                {{ $version->created_at->isoFormat('LL') }}
            </div>
            <div class="version-status">
                @if ($version->published)
                    {{ trans('messages.published') }}
                @else
                    <span>{{ trans('messages.unpublished') }}</span>
                @endif
            </div>
        </div>
        @unless ($version->published)
            <div class="rounded-pill bg-light d-flex justify-content-center align-items-center p-2 m-2 text-black fw-bold bg-light">{{ trans('messages.draft') }}</div>
        @else
            <div class="version-icons-20 d-flex justify-content-center align-items-center p-2 m-2">
                <span class="text-black fw-bold"><x-icon name="check2-circle" class="text-black"/></span>
            </div>
        @endunless
    </div>
</li>
