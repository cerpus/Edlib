<?php

namespace App\Apis;

use App\Util;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;

class AuthApiService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            "base_uri" => "http://authapi"
        ]);
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
}
