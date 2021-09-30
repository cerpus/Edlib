<?php

namespace App\Apis;

use App\Util;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;

class ContentAuthorService
{
    private Client $client;

    public function __construct(string $contentAuthorUrl, private string $contentAuthorInternalApiKey)
    {
        $this->client = new Client([
            "base_uri" => $contentAuthorUrl
        ]);
    }

    /**
     * @param array $data
     * @return PromiseInterface<array>
     */
    public function generateH5pFromQA(array $data): PromiseInterface
    {
        return $this->client
            ->postAsync('/internal/v1/contenttypes/questionsets', [
                'json' => $data,
                'headers' => [
                    'x-api-key' => $this->contentAuthorInternalApiKey
                ]
            ])
            ->then(fn($response) => Util::decodeResponse($response));
    }
}
