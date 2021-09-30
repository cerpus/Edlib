<?php

namespace App\Apis;

use App\ApiModels\LtiUsage;
use App\Util;
use GuzzleHttp\Client;

class LtiApiService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            "base_uri" => "http://ltiapi"
        ]);
    }

    /**
     * @param string $resourceId
     * @param string|null $resourceVersionId
     * @return LtiUsage
     */
    public function createUsage(string $resourceId, ?string $resourceVersionId): LtiUsage
    {
        $data = [
            'resourceId' => $resourceId,
            'resourceVersionId' => $resourceVersionId,
        ];

        $ltiUsageData = $this->client
            ->postAsync('/v1/usages', [
                'json' => $data
            ])
            ->then(fn($response) => Util::decodeResponse($response))->wait();

        return new LtiUsage(...$ltiUsageData);
    }
}
