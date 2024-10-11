<?php

declare(strict_types=1);

namespace App\Apis;

use App\Util;
use GuzzleHttp\Client;

class LtiApiService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'http://ltiapi',
        ]);
    }

    public function getResourceFromUsageId(string $usageId): array
    {
        return Util::handleEdlibNodeApiRequest(
            function () use ($usageId) {
                return $this->client
                    ->getAsync("/v1/usages/" . $usageId)
                    ->wait();
            }
        );
    }
}
