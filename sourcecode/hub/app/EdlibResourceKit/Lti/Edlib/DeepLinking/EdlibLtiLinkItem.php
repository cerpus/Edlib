<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Edlib\DeepLinking;

use App\EdlibResourceKit\Lti\Message\DeepLinking\LtiLinkItem;

use function array_values;

class EdlibLtiLinkItem extends LtiLinkItem
{
    private string|null $edlibVersionId = null;

    private string|null $languageIso639_3 = null;

    private string|null $license = null;

    private bool|null $published = null;

    private bool|null $shared = null;

    private string|null $ownerEmail = null;

    private string|null $contentType = null;

    private string|null $contentTypeName = null;

    /**
     * @var list<string>
     */
    private array $tags = [];

    public function getEdlibVersionId(): string|null
    {
        return $this->edlibVersionId;
    }

    public function withEdlibVersionId(string|null $edlibVersionId): static
    {
        $self = clone $this;
        $self->edlibVersionId = $edlibVersionId;

        return $self;
    }

    public function getLanguageIso639_3(): string|null
    {
        return $this->languageIso639_3;
    }

    public function withLanguageIso639_3(string|null $languageIso639_3): static
    {
        $self = clone $this;
        $self->languageIso639_3 = $languageIso639_3;

        return $self;
    }

    public function getLicense(): string|null
    {
        return $this->license;
    }

    public function withLicense(string|null $license): static
    {
        $self = clone $this;
        $self->license = $license;

        return $self;
    }

    public function isPublished(): bool|null
    {
        return $this->published;
    }

    public function withPublished(bool|null $published): static
    {
        $self = clone $this;
        $self->published = $published;

        return $self;
    }

    public function isShared(): bool|null
    {
        return $this->shared;
    }

    public function withShared(bool|null $shared): static
    {
        $self = clone $this;
        $self->shared = $shared;

        return $self;
    }

    /**
     * @return list<string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param string[] $tags
     */
    public function withTags(array $tags): static
    {
        $self = clone $this;
        $self->tags = array_values($tags);

        return $self;
    }

    public function getOwnerEmail(): string|null
    {
        return $this->ownerEmail;
    }

    public function withOwnerEmail(string|null $email): static
    {
        $self = clone $this;
        $self->ownerEmail = $email;

        return $self;
    }

    public function getContentType(): string|null
    {
        return $this->contentType;
    }

    public function withContentType(string|null $type): static
    {
        $self = clone $this;
        $self->contentType = $type;

        return $self;
    }

    public function getContentTypeName(): string|null
    {
        return $this->contentTypeName;
    }

    public function withContentTypeName(string|null $name): static
    {
        $self = clone $this;
        $self->contentTypeName = $name;

        return $self;
    }
}
