<?php

namespace App\ApiModels;

class Resource
{
    public string $id;
    public string $resourceGroupId;
    public ?string $deletedReason;
    public ?string $deletedAt;
    public string $updatedAt;
    public string $createdAt;

    public function __construct(
        string  $id,
        string  $resourceGroupId,
        ?string $deletedReason,
        ?string $deletedAt,
        string  $updatedAt,
        string  $createdAt
    ) {
        $this->id = $id;
        $this->resourceGroupId = $resourceGroupId;
        $this->deletedReason = $deletedReason;
        $this->deletedAt = $deletedAt;
        $this->updatedAt = $updatedAt;
        $this->createdAt = $createdAt;
    }
}
