<?php

namespace App\Libraries\DataObjects;


class EdlibResourceDataObject
{
    public $externalSystemName = "contentAuthor";
    public $externalSystemId;
    public $title;
    public $ownerId;
    public $isListed;
    public $isPublished;
    public $language;
    public $contentType;
    public $license;
    public $maxScore;
    public $createdAt;
    public $updatedAt;
    public $emailCollaborators;
    public $collaborators;
    public $authorOverwrite;

    public function __construct(
        $id,
        string $title,
        string $ownerId,
        bool $isListed,
        bool $isPublished,
        string $language,
        string $contentType,
        $license,
        $maxScore,
        $createdAt,
        $updatedAt,
        $collaborators = [],
        $emailCollaborators = [],
        $authorOverwrite
    )
    {
        $actualLicense = $license;
        if ($actualLicense == "") {
            $actualLicense = null;
        }

        $this->externalSystemId = $id;
        $this->title = $title;
        $this->ownerId = $ownerId;
        $this->isListed = $isListed;
        $this->isPublished = $isPublished;
        $this->language = $language;
        $this->contentType = $contentType;
        $this->license = $actualLicense;
        $this->maxScore = $maxScore;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->emailCollaborators = $emailCollaborators;
        $this->collaborators = $collaborators;
        $this->authorOverwrite = $authorOverwrite;
    }
}
