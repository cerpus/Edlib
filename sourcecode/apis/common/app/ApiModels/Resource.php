<?php

namespace App\ApiModels;

use App;
use App\Apis\ResourceApiService;

class Resource
{
    public function __construct(
        public string  $id,
        public string  $resourceGroupId,
        public ?string $deletedReason,
        public ?string $deletedAt,
        public string  $updatedAt,
        public string  $createdAt,
    )
    {}

    public static function getById(string $id): Resource
    {
        return App::call(fn(ResourceApiService $resourceApiService) => $resourceApiService->getResource($id));
    }

    public function getPublishedResourceVersion(): ?ResourceVersion
    {
        return App::call(fn(ResourceApiService $resourceApiService) => $resourceApiService->getPublishedResourceVersion($this->id));
    }
}
