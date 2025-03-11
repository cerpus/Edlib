<x-layout no-header no-nav no-footer>
    <x-form action="{{ route('lti.samples.deep-link.return') }}" method="POST" lang="en">
        @foreach ($errors->all() as $error)
            <p class="text-danger">{{ $error }}</p>
        @endforeach

        <p>
            <label class="d-block">
                Payload
                <textarea
                    class="form-control font-monospace w-100"
                    name="payload"
                    rows="12"
                >{{ json_encode([
                    '@context' => 'http://purl.imsglobal.org/ctx/lti/v1/ContentItem',
                    '@graph' => [
                        [
                            '@type' => 'LtiLinkItem',
                            'mediaType' => 'application/vnd.ims.lti.v1.ltilink',
                            'url' => route('lti.samples.presentation', [\App\Support\SessionScope::TOKEN_PARAM => null]),
                            'title' => 'My example content',
                        ],
                    ],
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</textarea>
            </label>
        </p>

        <p><button class="btn btn-primary">Send</button></p>
    </x-form>
</x-layout>
