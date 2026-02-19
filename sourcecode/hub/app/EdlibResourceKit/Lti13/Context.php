<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti13;

use App\EdlibResourceKit\Lti13\Attribute\Claim;

/**
 * @see http://www.imsglobal.org/spec/lti/v1p3/#context-claim
 */
class Context
{
    /**
     * @param non-empty-array<string>|null $type
     */
    public function __construct(
        #[Claim] private readonly string $id,
        #[Claim] private readonly array|null $type = [],
        #[Claim] private readonly string|null $label = null,
        #[Claim] private readonly string|null $title = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): array|null
    {
        return $this->type;
    }

    public function getLabel(): string|null
    {
        return $this->label;
    }

    public function getTitle(): string|null
    {
        return $this->title;
    }
}
