<?php

namespace App\Apis;

use App\User;
use App\Util;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use JetBrains\PhpStorm\ArrayShape;

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

    /**
     * @param string $externalToken
     * @return array
     * @throws \App\Exceptions\NotFoundException
     * @throws \App\Exceptions\UnauthorizedException
     * @throws \JsonException
     */
    #[ArrayShape(['token' => "mixed", 'user' => "\App\User"])] public function convertToken(string $externalToken): array
    {
        $convertedInfo = Util::handleEdlibNodeApiRequest(fn() => $this->client
            ->postAsync('/v1/convert-token', [
                'json' => [
                    'externalToken' => $externalToken
                ]
            ])
            ->wait()
        );

        return [
            'token' => $convertedInfo['token'],
            'user' => new User([
                'id' => $convertedInfo["user"]["id"],
                'firstName' => $convertedInfo["user"]['firstName'],
                'lastName' => $convertedInfo["user"]['lastName'],
                'email' => $convertedInfo["user"]['email'],
                'isAdmin' => $convertedInfo["user"]['isAdmin'] == 1,
            ])
        ];
    }
}
