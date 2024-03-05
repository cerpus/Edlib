@props(['filter'])
<div class="col-md-10 col-lg-10 d-md-block d-none mt-3">
    <form
        hx-get="{{ url()->current() }}"
        hx-target="#content"
        hx-swap="outerHTML"
        hx-replace-url="true"
        hx-include="#topFilterQuery"
        hx-trigger="submit, change"
        hx-sync="closest form:abort"
        hx-indicator="#content-loading"
        id="filterSelectedForm"
    >
        @foreach($filter->getContentTypeOptions() as $value => $label)
            @if(in_array($value, $filter->getContentTypes()))
                <x-filter.badge
                    type="checkbox"
                    aria-description="{{trans('messages.filter-remove')}}"
                    :value="$value"
                    name="ct[]"
                    checked
                    removable
                >
                    {{$label}}
                </x-filter.badge>
            @endif
        @endforeach
        @if ($filter->getLanguage() !== '')
            <x-filter.badge
                type="checkbox"
                value="{{$filter->getLanguage()}}"
                name="language"
                aria-hidden="true"
                checked
                removable
            >
                {{ $filter->getLanguageOptions()[$filter->getLanguage()] }}
            </x-filter.badge>
        @endif
    </form>
    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
        document.querySelector("#filterSelectedForm")
            .querySelectorAll("button[type='submit']")
            .forEach(button => button.addEventListener('click', event => {
                event.target.parentElement.querySelector("input").checked = false;
            }));
    </script>
</div>
