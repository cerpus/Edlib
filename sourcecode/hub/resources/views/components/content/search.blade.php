{{-- TODO: hitting enter/the button should not reload --}}
<form action="" method="GET" class="row g-3 mb-3">
    <div class="container mt-3">
        <div class="row g-3 align-items-center">
            <div class="col-8 col-md-5 col-lg-6">
                <div class="input-group search-container">
                    <x-form.input
                        wire:model="query"
                        name="q"
                        type="search"
                        :value="$query"
                        :aria-label="trans('messages.search')"
                        class="form-control border-0"
                        placeholder="Type to Search..."
                    />

                    <div class="input-group-prepend">
                        <span class="input-group-text bg-transparent border-0" id="search-icon">
                            <i class="bi bi-search"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-lg-3 d-md-block d-none">
                <select class="form-select" id="languageDropdown">
                    <option value="language1">Language 1</option>
                    <option value="language2">Language 2</option>
                    <option value="language3">Language 3</option>
                    <option value="language4">Language 4</option>
                    <option value="language5">Language 5</option>
                    <option value="language6">Language 6</option>
                </select>
            </div>
            <div class="col-md-4 col-lg-3 d-md-block d-none">
                <select class="form-select" id="lastChangedDropdown">
                    <option value="lastChanged1">Last Changed 1</option>
                    <option value="lastChanged2">Last Changed 2</option>
                    <option value="lastChanged3">Last Changed 3</option>
                    <option value="lastChanged4">Last Changed 4</option>
                    <option value="lastChanged5">Last Changed 5</option>
                    <option value="lastChanged6">Last Changed 6</option>
                </select>
            </div>
            <div class="col-4 col-lg-3">
                <button
                    id="filterButton"
                    class="btn btn-secondary d-lg-none d-md-none filter-button mobile-search-button"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasBottomMobile"
                    aria-controls="offcanvasBottomMobile"
                >
                    <i class="bi bi-filter me-1"></i>Filter
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-6 d-md-block d-none">
        <div class="">
            <span class="col-auto badge text-bg-primary">
                Media
                <i class="bi bi-x"></i>
            </span>
            <span class="col-auto badge text-bg-primary">
                Interactive Video
                <i class="bi bi-x"></i>
            </span>
            <span class="col-auto badge text-bg-primary">
                Text
                <i class="bi bi-x"></i>
            </span>

        </div>
        <div class="mt-1">
            <span class="col-auto badge text-bg-primary">
                Cerpus Image
                <i class="bi bi-x"></i>
            </span>
        </div>

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
