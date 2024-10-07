<?php

declare(strict_types=1);

namespace Feature;

use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class HealthTest extends TestCase
{
    public function testPerformsHealthcheck(): void
    {
        Event::fake(DiagnosingHealth::class);

        $this->get('/up')
            ->assertOk()
            ->assertSeeText('ok');

        Event::assertDispatched(DiagnosingHealth::class);
    }
}
