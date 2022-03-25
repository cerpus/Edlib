<?php

declare(strict_types=1);

namespace App\Edlib;

use Cerpus\EdlibResourceKit\Contract\EdlibResource;
use DateTimeImmutable;

class DokuEdlibResource implements EdlibResource
{
    public function __construct(
        private string $id,
        private string $title,
        private string $creatorId,
        private bool $public,
        private bool $draft,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public function getExternalSystemName(): string
    {
        return 'doku';
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
        return $this->creatorId;
    }

    public function isPublished(): bool
    {
        return $this->public;
    }

    public function isListed(): bool
    {
        return !$this->draft;
    }

    public function getLanguage(): string|null
    {
        return null;
    }

    public function getContentType(): string|null
    {
        return 'doku';
    }

    public function getLicense(): string|null
    {
        return null;
    }

    public function getMaxScore(): int|float|null
    {
        return null;
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
        return [];
    }

    public function getEmailCollaborators(): array
    {
        return [];
    }
}
