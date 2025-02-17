@if ($paginator->hasPages())
    <nav class="d-flex flex-column flex-lg-row">
        <div class="d-flex flex-fill align-self-start align-self-lg-center">
            <p class="small text-muted">
                {!! __('pagination.Showing') !!}
                <span class="fw-semibold">{{ $paginator->firstItem() }}</span>
                {!! __('pagination.to') !!}
                <span class="fw-semibold">{{ $paginator->lastItem() }}</span>
                {!! __('pagination.of') !!}
                <span class="fw-semibold">{{ $paginator->total() }}</span>
                {!! __('pagination.results') !!}

                <span class="d-block d-md-none">
                    {{ __('pagination.page') }}
                    <span class="fw-semibold">{{ $paginator->currentPage() }}</span>
                    {{ __('pagination.of') }}
                    <span class="fw-semibold">{{ $paginator->lastPage() }}</span>
                </span>
            </p>
        </div>

        <div class="d-flex d-md-none">
            <ul class="pagination d-flex flex-fill justify-content-center">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item d-flex disabled text-center flex-fill" aria-disabled="true">
                        <span class="page-link w-100">@lang('pagination.previous')</span>
                    </li>
                @else
                    <li class="page-item d-flex text-center flex-fill">
                        <a class="page-link w-100" href="{{ $paginator->previousPageUrl() }}" rel="prev">@lang('pagination.previous')</a>
                    </li>
                @endif
                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item d-flex text-center flex-fill">
                        <a class="page-link w-100" href="{{ $paginator->nextPageUrl() }}" rel="next">@lang('pagination.next')</a>
                    </li>
                @else
                    <li class="page-item d-flex disabled text-center flex-fill" aria-disabled="true">
                        <span class="page-link w-100">@lang('pagination.next')</span>
                    </li>
                @endif
            </ul>
        </div>

        <div class="d-none flex-md-fill d-md-flex align-items-md-center justify-content-lg-end justify-content-center">
            <div>
                <ul class="pagination">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link" aria-hidden="true">&lsaquo;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="page-link" aria-hidden="true">&rsaquo;</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
