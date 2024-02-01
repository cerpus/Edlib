<div class="position-sticky">
    <div class="accordion p-3 m-0 border-0 m-0 border-0" id="filterAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button id="collapseLanguageFilterHeader" class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLanguageFilter" aria-expanded="true" aria-controls="collapseLanguageFilter">
                    {{ trans('messages.language') }}
                </button>
            </h2>
            <div id="collapseLanguageFilter" class="accordion-collapse collapse show" data-bs-parent="#filterAccordion">
                <div class="accordion-body p-0">
                    <ul class="list-group">
                        <li class="list-group-item border-0">
                            <label class="form-check-label">
                                <input
                                    wire:model="filterLang"
                                    class="form-check-input me-1"
                                    type="radio"
                                    value=""
                                    name="fl"
                                >
                                {{ trans('messages.filter-language-all') }}
                            </label>
                        </li>
                        @foreach($languageOptions as $key => $label)
                            <li class="list-group-item border-0">
                                <label class="form-check-label">
                                    <input
                                        wire:model="filterLang"
                                        class="form-check-input me-1"
                                        type="radio"
                                        value="{{$key}}"
                                        name="fl"
                                    >
                                    {{ $label }}
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
