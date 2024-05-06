<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
    document.querySelectorAll("#collapseFilter select").forEach(elm => {
        if (elm.choices) {
            switch(elm.id) {
                case 'filterContentType':
                    replaceChoicesJsOptions(elm, JSON.parse('@json($filter->getContentTypeOptions())'));
                    break;
                case 'filterLanguage':
                    replaceChoicesJsOptions(elm, Object.assign(
                        {},
                        {'': '{{trans('messages.filter-language-all')}}'},
                        JSON.parse('@json($filter->getLanguageOptions())')
                    ));
                    break;
            }
        }
    });
    @if($filter->activeCount())
        document.getElementById("filterActiveCountLabel").classList.remove('visually-hidden');
        document.getElementById("filterActiveCount").innerText = '{{$filter->activeCount()}}';
        document.getElementById("filterClearButton").classList.remove('disabled');
        document.getElementById("filterClearButton").setAttribute('aria-disabled', 'false');
    @else
        document.getElementById("filterActiveCountLabel").classList.add('visually-hidden');
        document.getElementById("filterActiveCount").innerText = '0';
        document.getElementById("filterClearButton").classList.add('disabled');
        document.getElementById("filterClearButton").setAttribute('aria-disabled', 'true');
    @endif
</script>
