<?php

namespace App\Apis;

use App\ApiModels\ResourceCollaborator;
use App\Exceptions\NotFoundException;
use App\Util;
use GuzzleHttp\Client;

class ResourceApiService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            "base_uri" => "http://resourceapi"
        ]);
    }

    /**
     * @return ResourceCollaborator[]
     * @throws NotFoundException
     * @throws \JsonException
     */
    public function getCollaborators(string $externalSystemName, string $externalSystemId): array
    {
        $data = Util::handleEdlibNodeApiRequest(function () use ($externalSystemName, $externalSystemId) {
            return $this->client
                ->getAsync("/v1/resources-from-external/$externalSystemName/$externalSystemId/collaborators")
                ->wait();
        }
        );

        return array_map(function ($collaborator) {
            return new ResourceCollaborator($collaborator['tenantId']);
        }, $data["collaborators"]);
    }
}
