<?php

namespace App\ApiModels;

class ResourceVersion
{
    public string $id;
    public string $resourceId;
    public string $externalSystemName;
    public string $externalSystemId;
    public string $title;
    public ?string $description;
    public bool $isPublished;
    public bool $isListed;
    public bool $isDraft;
    public string $license;
    public bool $language;
    public string $contentType;
    public string $ownerId;
    public ?int $maxScore;
    public ?string $authorOverwrite;
    public string $updatedAt;
    public string $createdAt;

    public function __construct($data)
    {
        $this->id = $data["id"];
        $this->resourceId = $data["resourceId"];
        $this->externalSystemName = $data["externalSystemName"];
        $this->externalSystemId = $data["externalSystemId"];
        $this->title = $data["title"];
        $this->description = $data["description"];
        $this->isPublished = $data["isPublished"];
        $this->isListed = $data["isListed"];
        $this->isDraft = $data["isDraft"];
        $this->license = $data["license"];
        $this->language = $data["language"];
        $this->contentType = $data["contentType"];
        $this->ownerId = $data["ownerId"];
        $this->maxScore = $data["maxScore"];
        $this->authorOverwrite = $data["authorOverwrite"];
        $this->updatedAt = $data["updatedAt"];
        $this->createdAt = $data["createdAt"];
    }
}
