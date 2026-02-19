<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti13\Mapping;

/**
 * How to read a field's value.
 */
final readonly class Read
{
    public function __construct(
        private string $name,
        private ReadType $type,
        private bool $private = false,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ReadType
    {
        return $this->type;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }
}
