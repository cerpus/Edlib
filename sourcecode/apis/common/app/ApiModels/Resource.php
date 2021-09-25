<?php

namespace App\ApiModels;

use App;
use App\Apis\ResourceApiService;

class Resource
{
    public string $id;
    public string $resourceGroupId;
    public ?string $deletedReason;
    public ?string $deletedAt;
    public string $updatedAt;
    public string $createdAt;

    public function __construct($data)
    {
        $this->id = $data["id"];
        $this->resourceGroupId = $data["resourceGroupId"];
        $this->deletedReason = $data["deletedReason"];
        $this->deletedAt = $data["deletedAt"];
        $this->updatedAt = $data["updatedAt"];
        $this->createdAt = $data["createdAt"];
    }

    public static function getById(string $id) : Resource
    {
        return App::call(fn(ResourceApiService $resourceApiService) => $resourceApiService->getResource($id));
    }

    public function getPublishedResourceVersion(): ?ResourceVersion
    {
        return App::call(fn(ResourceApiService $resourceApiService) => $resourceApiService->getPublishedResourceVersion($this->id));
    }
}
