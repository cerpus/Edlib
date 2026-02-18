<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\FactoryImmutable;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final readonly class CarbonToPsrClockAdapter implements ClockInterface
{
    public function __construct(private FactoryImmutable $factory) {}

    public function now(): DateTimeImmutable
    {
        return $this->factory->now();
    }
}
