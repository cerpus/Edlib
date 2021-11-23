<?php

namespace App\Apis;

use App\ApiModels\LtiUser;
use App\Util;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\ArrayShape;

class AuthApiService
{
    private Client $client;
    private string $baseUrl = "http://authapi";

    public function __construct()
    {
        $this->client = new Client([
            "base_uri" => $this->baseUrl
        ]);
    }

    private function getUrl(string $path): string
    {
        return $this->baseUrl . $path;
    }

    /**
     * @return PromiseInterface<array>
     */
    public function getJwks(): PromiseInterface
    {
        return $this->client
            ->getAsync('/.well-known/jwks.json')
            ->then(fn($response) => Util::decodeResponse($response));
    }

    #[ArrayShape(['token' => "string", 'userId' => "string"])] public function createTokenForLtiUser(LtiUser $ltiUser): array
    {
        $response = Http::post($this->getUrl('/v1/lti-users/token'), (array) $ltiUser);

        return [
            'token' => $response['token'],
            'userId' => $response['user']['id']
        ];
    }
}
