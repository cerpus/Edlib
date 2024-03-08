@props(['filter'])

<div class="position-sticky">
    <div class="accordion p-3 m-0 border-0 m-0 border-0">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button id="collapseLanguageFilterHeader" class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLanguageFilter" aria-expanded="true" aria-controls="collapseLanguageFilter">
                    {{ trans('messages.language') }}
                </button>
            </h2>
            <div id="collapseLanguageFilter" class="accordion-collapse collapse show">
                <div class="accordion-body p-0">
                    <ul class="list-group">
                        <li class="list-group-item border-0">
                            <label class="form-check-label">
                                <input
                                    class="form-check-input me-1"
                                    type="radio"
                                    value=""
                                    name="language"
                                    @checked($filter->getLanguage() === '')
                                >
                                {{ trans('messages.filter-language-all') }}
                            </label>
                        </li>
                        @foreach($filter->getLanguageOptions() as $key => $label)
                            <li class="list-group-item border-0">
                                <label class="form-check-label">
                                    <input
                                        class="form-check-input me-1"
                                        type="radio"
                                        value="{{$key}}"
                                        name="language"
                                        @checked($key === $filter->getLanguage())
                                    >
                                    {{ $label }}
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button id="collapseContentTypeHeader" class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContentType" aria-expanded="true" aria-controls="collapseContentType">
                    {{ trans('messages.content-type') }}
                </button>
            </h2>
            <div id="collapseContentType" class="accordion-collapse collapse show">
                <div class="accordion-body p-0">
                    <ul class="list-group">
                        @foreach($filter->getContentTypeOptions() as $value => $label)
                            <li class="list-group-item border-0">
                                <label class="form-check-label">
                                    <input
                                        class="form-check-input me-1"
                                        type="checkbox"
                                        value="{{$value}}"
                                        name="type[]"
                                        @checked(in_array($value, $filter->getContentTypes()))
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
