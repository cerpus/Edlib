<section id="filterSection" class="col-md-3 col-lg-2 d-md-block bg-white sidebar" role="complementary" aria-labelledby="filterSectionLabel">
    <h2 id="filterSectionLabel" class="visually-hidden">Filter Section</h2>
    <div class="position-sticky">
        <div class="accordion p-3 m-0 border-0 bd-example m-0 border-0" id="filterAccordion">
            @foreach (['Language', 'Media', 'Visuals', 'Task', 'Quizzes'] as $filter)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button id="collapse{{ $filter }}" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $filter }}Filter" aria-expanded="false" aria-controls="collapse{{ $filter }}Filter">
                            {{ $filter }}
                        </button>
                    </h2>
                    <div id="collapse{{ $filter }}Filter" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
                        <div class="accordion-body p-0">
                            <ul class="list-group">
                                @for ($i = 1; $i <= 3; $i++)
                                    <li class="list-group-item border-0">
                                        <label class="form-check-label" for="{{ strtolower($filter) }}{{ $i }}">
                                            <input class="form-check-input me-1" type="checkbox" value="" id="{{ strtolower($filter) }}{{ $i }}">
                                            {{ $filter }} {{ $i }}
                                        </label>
                                    </li>
                                @endfor
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
