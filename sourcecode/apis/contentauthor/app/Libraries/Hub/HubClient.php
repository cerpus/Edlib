<?php

declare(strict_types=1);

namespace App\Libraries\Hub;

use App\Lti\LtiRequest;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;

class HubClient
{
    private readonly Credentials $credentials;

    public function __construct(
        private readonly SignerInterface $signer,
        private readonly Client $client,
    ) {
        $this->credentials = new Credentials(
            config('app.consumer-key'),
            config('app.consumer-secret'),
        );
    }

    /**
     * Make an OAuth1-signed POST request to Hub.
     *
     * Relative paths (e.g. '/content/info') are resolved against the base
     * URL from the LTI session. Absolute URLs are used as-is.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException|\JsonException
     */
    public function post(string $url, array $params = []): array
    {
        if (!str_starts_with($url, 'http')) {
            $url = $this->baseUrl() . $url;
        }

        $oauthRequest = new Oauth1Request('POST', $url, $params);
        $oauthRequest = $this->signer->sign($oauthRequest, $this->credentials);

        $response = $this->client->post($url, [
            'form_params' => $oauthRequest->toArray(),
        ])
            ->getBody()
            ->getContents();

        return json_decode($response, associative: true, flags: JSON_THROW_ON_ERROR);
    }

    public function baseUrl(): string
    {
        /** @var LtiRequest $ltiRequest */
        $ltiRequest = Session::get('lti_requests.admin');

        return $ltiRequest->param('ext_edlib3_author_endpoint');
    }
}
