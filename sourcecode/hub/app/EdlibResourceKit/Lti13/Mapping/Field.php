<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti13\Mapping;

class Field
{
    public function __construct(
        private readonly string $claim,
        private Read|null $read = null,
        private Write|null $write = null,
    ) {
    }

    public function getClaim(): string
    {
        return $this->claim;
    }

    public function getRead(): Read
    {
        return $this->read;
    }

    public function withRead(Read $read): static
    {
        $self = clone $this;
        $self->read = $read;

        return $self;
    }

    public function getWrite(): Write
    {
        return $this->write;
    }

    public function withWrite(Write $write): static
    {
        $self = clone $this;
        $self->write = $write;

        return $self;
    }
}
