<?php

namespace App\Apis;

use App\ApiModels\Resource;
use App\ApiModels\ResourceVersion;
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
     */
    public function getResource(string $resourceId): Resource
    {
        $resourceData = $this->client
            ->getAsync('/v1/resources/' . $resourceId)
            ->then(fn($response) => Util::decodeResponse($response))->wait();

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
     */
    public function setResourceCollaborators(string $applicationId, string $context, array $tenantIds, ?array $resourceIds, ?array $externalResources): bool
    {
        $data = [
            'applicationId' => $applicationId,
            'context' => $context,
            'resourceIds' => $resourceIds,
            'tenantIds' => $tenantIds
        ];

        if ($externalResources !== null) {
            $data['externalResources'] = $externalResources;
        }

        $this->client
            ->postAsync('/v1/context-resource-collaborators', [
                'json' => $data
            ])
            ->then(fn($response) => Util::decodeResponse($response))->wait();

        return true;
    }

    /**
     * @param string $externalSystemName
     * @param string $externalSystemId
     * @return ResourceVersion
     */
    public function ensureResourceExists(string $externalSystemName, string $externalSystemId): ResourceVersion
    {
        $resourceVersionData = $this->client
            ->postAsync("/v1/external-systems/$externalSystemName/resources/$externalSystemId")
            ->then(fn($response) => Util::decodeResponse($response))->wait();

        return new ResourceVersion(...$resourceVersionData);
    }
}
