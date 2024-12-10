<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
    @if($filter->shouldUpdateContentTypeOptions())
        replaceChoicesJsOptions(
            document.getElementById('filterContentType'),
            JSON.parse('@json($filter->getContentTypeOptions(withExpectedHits: true))')
        );
    @endif
    @if($filter->shouldUpdateLanguageOptions())
        replaceChoicesJsOptions(
            document.getElementById('filterLanguage'),
            JSON.parse('@json($filter->getLanguageOptions(withExpectedHits: true))'));
    @endif
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
