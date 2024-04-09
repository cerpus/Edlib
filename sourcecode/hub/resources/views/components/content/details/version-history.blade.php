@php $content = $version->content; @endphp
<section>
    <h2 class="fs-5">{{ trans('messages.version-history') }}</h2>

    <ul class="list-unstyled d-flex flex-column gap-2 mb-0 version-history">
        @foreach ($content->versions as $v)
            @php
                $isLatest = $content->latestVersion->is($v);
                $isCurrent = $version->is($v);
            @endphp

            <li
                @class([
                    'position-relative',
                    'text-body',
                    'p-3',
                    'border',
                    'rounded',
                    'd-flex',
                    'border-success-subtle' => $v->published,
                    'published' => $v->published,
                    'draft' => !$v->published,
                    'bg-success-subtle' => $content->latestPublishedVersion?->is($v),
                ])
            >
                <span class="flex-grow-1">
                    <a
                        href="{{ route('content.version-details', [$content, $v]) }}"
                        @class([
                            'd-block',
                            'text-body',
                            'link-underline-opacity-0',
                            'link-underline-opacity-100-hover',
                            'stretched-link',
                            'link-underline-success' => $v->published,
                            'link-underline' => !$v->published,
                            'fw-bold' => $isCurrent,
                        ])
                    >
                        <time datetime="{{ $v->created_at->toIso8601String() }}"></time>
                    </a>

                    @if ($content->latestPublishedVersion?->is($v))
                        <small class="d-block">{{ trans('messages.current-published-version') }}</small>
                    @elseif ($content->latestDraftVersion?->is($v))
                        <small class="d-block">{{ trans('messages.latest-draft') }}</small>
                    @endif
                </span>

                @if ($v->published)
                    <x-icon name="check-circle" label="{{ trans('messages.published') }}" class="text-success" />
                @else
                    <x-icon name="pencil" label="{{ trans('messages.draft') }}" class="text-body-secondary" />
                @endif
            </li>
        @endforeach
    </ul>
</section>
