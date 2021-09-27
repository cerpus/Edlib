<?php

namespace App\ApiModels;

class ResourceVersion
{
    public function __construct(
        public string  $id,
        public string  $resourceId,
        public string  $externalSystemName,
        public string  $externalSystemId,
        public string  $title,
        public ?string $description,
        public bool    $isPublished,
        public bool    $isListed,
        public bool    $isDraft,
        public string  $license,
        public bool    $language,
        public string  $contentType,
        public string  $ownerId,
        public ?int    $maxScore,
        public ?string $authorOverwrite,
        public string  $updatedAt,
        public string  $createdAt
    )
    {}
}
