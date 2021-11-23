<?php

namespace App\Apis;

use App\ApiModels\Resource;
use App\ApiModels\ResourceLaunchInfo;
use App\ApiModels\ResourceVersion;
use App\Exceptions\NotFoundException;
use App\Util;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class ResourceApiService
{
    private Client $client;
    private string $baseUrl = "http://resourceapi";

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

    /**
     * @throws NotFoundException
     * @throws \JsonException
     */
    public function getResourceLaunchInfoForTenant(string $userId, string $resourceId, ?string $resourceVersionId = null): ResourceLaunchInfo
    {
        $resourceLaunchInfo = Util::handleEdlibNodeApiLaravelRequest(fn() => Http::get($this->getUrl("/v1/tenants/$userId/resources/$resourceId/launch-info"), [
            'resourceVersionId' => $resourceVersionId
        ]));

        return new ResourceLaunchInfo(...$resourceLaunchInfo);
    }
}
