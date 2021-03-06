<?php

declare(strict_types=1);

namespace App\EdlibResource;

use Cerpus\EdlibResourceKit\Contract\DraftAwareResource;
use DateTimeImmutable;

class CaEdlibResource implements DraftAwareResource
{
    /**
     * @param array<string> $collaborators
     * @param array<string> $emailCollaborators
     */
    public function __construct(
        private string $id,
        private string $title,
        private string $ownerId,
        private bool $published,
        private bool $isDraft,
        private bool $listed,
        private string|null $language,
        private string|null $contentType,
        private string|null $license,
        private int|float|null $maxScore,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        private array $collaborators,
        private array $emailCollaborators,
        private string|null $authorOverwrite = null,
    ) {
        if ($this->license === '') {
            $this->license = null;
        }
    }

    public function getExternalSystemName(): string
    {
        return 'contentAuthor';
    }

    public function getExternalSystemId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getOwnerId(): string|null
    {
        return $this->ownerId;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function isListed(): bool
    {
        return $this->listed;
    }

    public function getLanguage(): string|null
    {
        return $this->language;
    }

    public function getContentType(): string|null
    {
        return $this->contentType;
    }

    public function getLicense(): string|null
    {
        return $this->license;
    }

    public function getMaxScore(): int|float|null
    {
        return $this->maxScore;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getCollaborators(): array
    {
        return $this->collaborators;
    }

    public function getEmailCollaborators(): array
    {
        return $this->emailCollaborators;
    }

    public function getAuthorOverwrite(): string|null
    {
        return $this->authorOverwrite;
    }

    public function isDraft(): bool
    {
        return $this->isDraft;
    }
}
