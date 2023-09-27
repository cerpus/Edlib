<div class="position-sticky">
    <div class="accordion p-3 m-0 border-0 bd-example m-0 border-0" id="filterAccordion">
        @foreach (['language', 'media', 'visuals', 'task', 'quizzes'] as $filterKey)
            @php
                $filterLabel = trans("messages.$filterKey");
                $uniqueContextClass = \Illuminate\Support\Str::random(5);
                $collapseId = "collapse" . ucfirst($filterKey) . $uniqueContextClass;
            @endphp

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button id="{{ $collapseId . "Header" }}" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                        {{ $filterLabel }}
                    </button>
                </h2>
                <div id="{{ $collapseId }}" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
                    <div class="accordion-body p-0">
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
</div>
