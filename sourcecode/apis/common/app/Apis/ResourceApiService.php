<?php

namespace App\Apis;

use App\ApiModels\Resource;
use App\ApiModels\ResourceVersion;
use App\Exceptions\NotFoundException;
use App\Util;
use GuzzleHttp\Client;

class ResourceApiService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            "base_uri" => "http://resourceapi"
        ]);
    }

    /**
     * @param string $resourceId
     * @return Resource
     * @throws NotFoundException|\JsonException
     */
    public function getResource(string $resourceId): Resource
    {
        $resourceData = Util::handleEdlibNodeApiRequest(fn() => $this->client
            ->getAsync('/v1/resources/' . $resourceId)
            ->wait()
        );

        return new Resource(...$resourceData);
    }

    /**
     * @param string $resourceId
     * @return ResourceVersion
     */
    public function getPublishedResourceVersion(string $resourceId): ResourceVersion
    {
        $resourceData = $this->client
            ->getAsync('/v1/resources/' . $resourceId . '/version')
            ->then(fn($response) => Util::decodeResponse($response))->wait();

        return new ResourceVersion(...$resourceData);
    }

    /**
     * @param string $applicationId
     * @param string $context
     * @param string[] $tenantIds
     * @param string[] $resourceIds
     * @param array|null $externalResources
     * @return bool
     * @throws NotFoundException
     * @throws \JsonException
     */
    public function setResourceCollaborators(string $applicationId, string $context, array $tenantIds, ?array $resourceIds, ?array $externalResources): bool
    {
        $data = [
            'applicationId' => $applicationId,
            'context' => $context,
            'resourceIds' => $resourceIds ?? [],
            'externalResources' => $externalResources ?? [],
            'tenantIds' => $tenantIds
        ];

        Util::handleEdlibNodeApiRequest(fn() => $this->client
            ->postAsync('/v1/context-resource-collaborators', [
                'json' => $data
            ])
            ->wait()
        );

        return true;
    }

    /**
     * @param string $externalSystemName
     * @param string $externalSystemId
     * @return ResourceVersion
     * @throws \JsonException|NotFoundException
     */
    public function ensureResourceExists(string $externalSystemName, string $externalSystemId): ResourceVersion
    {
        $resourceVersionData = Util::handleEdlibNodeApiRequest(fn() => $this->client
            ->postAsync("/v1/external-systems/$externalSystemName/resources/$externalSystemId")
            ->wait()
        );

        return new ResourceVersion(...$resourceVersionData);
    }
}
