<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use App\Configuration\Features;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class FeaturesTest extends TestCase
{
    #[TestWith(['5s', '5s'])]
    #[TestWith(['10s', '10s'])]
    #[TestWith(['500ms', '500ms'])]
    #[TestWith(['5s', 5])]
    #[TestWith(['15s', '15'])]
    #[TestWith(['2.5s', '2.5'])]
    #[TestWith([null, null])]
    #[TestWith([null, false])]
    #[TestWith([null, 0])]
    #[TestWith([null, '0'])]
    #[TestWith([null, '0s'])]
    #[TestWith([null, ''])]
    #[TestWith([null, '   '])]
    #[TestWith([null, 'none'])]
    #[TestWith([null, 'NONE'])]
    #[TestWith([null, 'disabled'])]
    #[TestWith([null, 'DISABLED'])]
    #[TestWith([null, 'false'])]
    #[TestWith([null, 'off'])]
    #[TestWith([null, -5])]
    public function testFormatPollingInterval(string|null $expected, mixed $input): void
    {
        $this->assertSame($expected, Features::formatPollingInterval($input));
    }
}
