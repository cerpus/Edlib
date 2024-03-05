@props(['filter'])
@php($showMoreCutoff = 6)
<form
    hx-get="{{ url()->current() }}"
    hx-target="#content"
    hx-trigger="change from:find #sideFilterLanguage delay:500ms, change"
    hx-include="this, #topFilterQuery, #topFilterSort"
    hx-validate="true"
    hx-replace-url="true"
    hx-sync="closest form:abort"
    hx-swap="outerHTML"
    hx-indicator="#content-loading"
>
    <div class="row mt-3">
        <fieldset class="d-flex flex-row flex-wrap">
            <legend class="filter-side-legend" aria-description="{{trans('messages.filter-content-types-desc')}}">
                {{ trans('messages.content-type') }}
                @if(count($filter->getContentTypes()) > 0)
                    <span class="small">({{count($filter->getContentTypes())}})</span>
                @endif
            </legend>
            @foreach($filter->getContentTypeOptions() as $value => $label)
                @if($loop->iteration === $showMoreCutoff)
                    <div class="collapse" id="filterContentTypesMore">
                @endif
                        <x-filter.badge
                            :value="$value"
                            :checked="in_array($value, $filter->getContentTypes())"
                            inputType="checkbox"
                            name="ct[]"
                        >
                            {{$label}}
                        </x-filter.badge>
                @if($loop->last && $loop->count >= $showMoreCutoff)
                    </div>
                @endif
            @endforeach
        </fieldset>
        @if(count($filter->getContentTypeOptions()) >= $showMoreCutoff)
            <button
                type="button"
                class="btn btn-secondary border-0 btn-sm filter-contenttype-toggle-button collapsed"
                data-bs-toggle="collapse"
                data-bs-target="#filterContentTypesMore"
                aria-expanded="false"
                aria-controls="filterContentTypesMore"
            >
                <span class="flex-column filter-contenttype-more-content">
                    {{trans('messages.filter-toggle-more')}}
                    <x-icon name="chevron-down"/>
                </span>
                <span class="flex-column filter-contenttype-less-content">
                    {{trans('messages.filter-toggle-less')}}
                    <x-icon name="chevron-up"/>
                </span>
            </button>
        @endif
    </div>
    <div class="row mt-3 mb-3">
        <label aria-description="{{ trans('messages.filter-language') }}">
            {{ trans('messages.language') }}
            <x-form.dropdown
                id="sideFilterLanguage"
                name="language"
                :selected="$filter->getLanguage()"
                :aria-label="trans('messages.filter-language')"
                :options="$filter->getLanguageOptions()"
                :emptyOption="trans('messages.filter-language-all')"
            />
        </label>
    </div>
</form>
<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
    document.querySelectorAll(".filter-badge.filter-focus")
        .forEach(badge => badge.addEventListener('keyup', event => {
            if (event.key === 'Enter' || event.key === " ") {
                event.target.querySelector("input").click();
            }
        }));
</script>
