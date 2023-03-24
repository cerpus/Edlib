<?php

namespace Tests\Stub;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final class ClockStub implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('@1000000000');
    }
}
