<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti13\Mapping;

/**
 * How to write a field's value.
 */
final readonly class Write
{
    public function __construct(
        private string $name,
        private WriteType $type,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): WriteType
    {
        return $this->type;
    }
}
