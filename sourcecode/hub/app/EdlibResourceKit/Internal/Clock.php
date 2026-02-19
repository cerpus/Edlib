<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Internal;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * @internal This should not be used outside cerpus/edlib-resource-kit-laravel
 */
final class Clock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return CarbonImmutable::now();
    }
}
