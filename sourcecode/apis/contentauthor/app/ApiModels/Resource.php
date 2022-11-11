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
    public string|null $title;

    public function __construct(
        string  $id,
        string  $resourceGroupId,
        ?string $deletedReason,
        ?string $deletedAt,
        string  $updatedAt,
        string  $createdAt,
        string  $title = null
    ) {
        $this->id = $id;
        $this->resourceGroupId = $resourceGroupId;
        $this->deletedReason = $deletedReason;
        $this->deletedAt = $deletedAt;
        $this->updatedAt = $updatedAt;
        $this->createdAt = $createdAt;
        $this->title     = $title;
    }
}
