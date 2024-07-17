<?php

namespace App\Apis;

use App\ApiModels\Resource;
use App\ApiModels\ResourceCollaborator;
use App\Exceptions\NotFoundException;
use App\Util;
use GuzzleHttp\Client;
use JsonException;

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
     * @throws JsonException
     */
    public function getCollaborators(string $externalSystemName, string $externalSystemId): array
    {
        $data = Util::handleEdlibNodeApiRequest(
            function () use ($externalSystemName, $externalSystemId) {
                return $this->client
                    ->getAsync("/v1/resources-from-external/$externalSystemName/$externalSystemId/collaborators")
                    ->wait();
            }
        );

        return array_map(function ($collaborator) {
            return new ResourceCollaborator($collaborator['tenantId']);
        }, $data["collaborators"]);
    }

    /**
     * @throws NotFoundException
     * @throws JsonException
     */
    public function getResourceFromExternalReference(string $externalSystemName, string $externalSystemId): Resource
    {
        $data = Util::handleEdlibNodeApiRequest(function () use ($externalSystemName, $externalSystemId) {
            return $this->client
                ->getAsync("/v1/resources-from-external/$externalSystemName/$externalSystemId")
                ->wait();
        });

        return new Resource(
            $data['id'],
            $data['resourceGroupId'],
            $data['deletedReason'],
            $data['deletedAt'],
            $data['updatedAt'],
            $data['createdAt'],
            $data['version']['title']
        );
    }

    /**
     * @throws NotFoundException
     * @throws JsonException
     */
    public function getResourceById(string $resourceId): array
    {
        $data = Util::handleEdlibNodeApiRequest(function () use ($resourceId) {
            return $this->client
                ->getAsync("/v1/resources/$resourceId/version")
                ->wait();
        });

        if ($data['externalSystemName'] === 'contentauthor') {
            return $data;
        }

        throw new NotFoundException('Resource');
    }

    /**
     * @throws NotFoundException
     * @throws JsonException
     */
    public function getResourceByIdAndVersion(string $resourceId, string $versionId): array
    {
        $data = Util::handleEdlibNodeApiRequest(function () use ($resourceId, $versionId) {
            return $this->client
                ->getAsync("/v1/resources/$resourceId/versions/$versionId")
                ->wait();
        });

        if ($data['externalSystemName'] === 'contentauthor') {
            return $data;
        }

        throw new NotFoundException('Resource');
    }
}
