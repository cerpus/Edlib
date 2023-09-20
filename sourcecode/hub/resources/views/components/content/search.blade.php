{{-- TODO: hitting enter/the button should not reload --}}
<form action="" method="GET" class="row g-3 mb-3">
    <div class="col-12 col-lg-6">
        <div class="input-group">
            <label class="input-group-text" for="q">
                <x-icon name="search" label="{{ trans('messages.search-query') }}"/>
            </label>
            <x-form.input
                wire:model="query"
                name="q"
                type="search"
                :value="$query"
                :aria-label="trans('messages.search')"
            />
        </div>
    </div>
    <div class="col-auto">
        <x-form.button
            class="btn-secondary"
            :aria-label="trans('messages.search')"
        >
            <x-icon name="search" />
            {{ trans('messages.search') }}
        </x-form.button>

        <button
            id="filterButton"
            class="btn btn-secondary d-lg-none d-md-none filter-button"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#offcanvasBottomMobile"
            aria-controls="offcanvasBottomMobile"
        >
            <i class="bi bi-filter me-1"></i>Filter
        </button>
    </div>
</form>

<div class="offcanvas offcanvas-bottom" id="offcanvasBottomMobile" aria-labelledby="offcanvasBottomLabel" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="offcanvas-body">
        <div class="mb-2">
            <button
                id="backButton"
                class="btn btn-secondary d-lg-none filter-back-button backButton border-0"
                type="button"
                data-bs-dismiss="offcanvas"
            >
                <x-icon name="arrow-return-left" class="me-1" />
            </button>
        </div>

        <div class="accordion" id="filterAccordionMobile">
            @foreach (['language', 'media', 'visuals', 'task', 'quizzes'] as $index => $filterKey)
                @php
                    $filterLabel = trans("messages.$filterKey");
                    $customClass = $index === 0 ? 'first-accordion-item' : '';
                @endphp
                <div class="accordion-item {{ $customClass }} border-bottom">
                    <h2 class="accordion-header">
                        <button id="collapse{{ ucfirst($filterKey) }}Mobile" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $filterKey }}FilterMobile" aria-expanded="false" aria-controls="collapse{{ $filterKey }}FilterMobile">
                            {{ $filterLabel }}
                        </button>
                    </h2>
                    <div id="collapse{{ $filterKey }}FilterMobile" class="accordion-collapse collapse" data-bs-parent="#filterAccordionMobile">
                        <div class="accordion-body border-bottom">
                            <ul class="list-group">
                                @for ($i = 1; $i <= 3; $i++)
                                    <li class="list-group-item border-0">
                                        <label class="form-check-label">
                                            <input class="form-check-input me-1" type="checkbox" value="">
                                            {{ $filterLabel }} {{ $i }}
                                        </label>
                                    </li>
                                @endfor
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center">
            <button
                id="showResultsButton"
                class="btn btn-primary d-lg-none d-md-none filter-button mt-5"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#howResultsButtonMobile"
                aria-controls="howResultsButtonMobile"
            >
                Show 378 results
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const windowWidth = window.innerWidth;
        if (windowWidth <= 768) {
            new bootstrap.Offcanvas(document.getElementById('offcanvasBottomMobile'));
        }
    });
</script>
