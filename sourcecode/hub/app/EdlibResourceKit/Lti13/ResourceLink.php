<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti13;

use App\EdlibResourceKit\Lti13\Attribute\Claim;

/**
 * @see http://www.imsglobal.org/spec/lti/v1p3/#resource-link-claim
 */
class ResourceLink
{
    public function __construct(
        #[Claim] private readonly string $id,
        #[Claim] private readonly string|null $description,
        #[Claim] private readonly string|null $title,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDescription(): string|null
    {
        return $this->description;
    }

    public function getTitle(): string|null
    {
        return $this->title;
    }
}
