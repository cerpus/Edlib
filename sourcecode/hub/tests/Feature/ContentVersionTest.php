<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentVersion;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

final class ContentVersionTest extends TestCase
{
    /**
     * @param numeric-string $min
     * @param numeric-string $max
     */
    #[TestWith([false, '0.00', '0.00'])]
    #[TestWith([true, '1.00', '0.00'])]
    #[TestWith([true, '0.00', '1.00'])]
    public function testGivesScore(bool $expected, string $min, string $max): void
    {
        $v = new ContentVersion();
        $v->min_score = $min;
        $v->max_score = $max;

        $this->assertSame($expected, $v->givesScore());
    }
}
