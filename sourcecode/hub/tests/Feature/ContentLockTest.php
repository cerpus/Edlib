<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentLock;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

use function now;

#[CoversClass(ContentLock::class)]
class ContentLockTest extends TestCase
{
    use RefreshDatabase;

    public function testActiveScope(): void
    {
        $now = now()->toImmutable();
        Carbon::setTestNow($now);

        ContentLock::factory()->lockedAt($now->subSeconds(20))->create();
        ContentLock::factory()->lockedAt($now->subSeconds(40))->create();
        ContentLock::factory()->lockedAt($now->subSeconds(60))->create();
        ContentLock::factory()->lockedAt($now->subSeconds(80))->create();
        ContentLock::factory()->lockedAt($now->subSeconds(90))->create();
        ContentLock::factory()->lockedAt($now->subSeconds(100))->create();
        ContentLock::factory()->lockedAt($now->subSeconds(120))->create();

        $this->assertSame(5, ContentLock::active()->count());
    }

    public function testInactiveScope(): void
    {
        $now = now()->toImmutable();
        Carbon::setTestNow($now);

        ContentLock::factory()->lockedAt($now->subSeconds(20))->create();
        ContentLock::factory()->lockedAt($now->subSeconds(40))->create();
        ContentLock::factory()->lockedAt($now->subSeconds(60))->create();
        ContentLock::factory()->lockedAt($now->subSeconds(80))->create();
        ContentLock::factory()->lockedAt($now->subSeconds(90))->create();
        ContentLock::factory()->lockedAt($now->subSeconds(100))->create();
        ContentLock::factory()->lockedAt($now->subSeconds(120))->create();

        $this->assertSame(2, ContentLock::inactive()->count());
    }
}
