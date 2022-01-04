<?php

namespace Tests\Commands;

use Carbon\Carbon;
use Tests\TestCase;
use App\ContentLock;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RemoveOldLocksTest extends TestCase
{
    use RefreshDatabase;

    public function testOldLocksAreRemoved()
    {
        ContentLock::factory()->count(2)->create(
            [
                'created_at' => Carbon::now()->subMinutes(ContentLock::EXPIRES),
                'updated_at' => Carbon::now()->subMinutes(ContentLock::EXPIRES - 2),
            ]);

        $this->assertCount(2, ContentLock::all());

        Artisan::call('cerpus:remove-content-locks', []);

        $this->assertCount(2, ContentLock::all());

        ContentLock::factory()->count(3)->create(
            [
                'created_at' => Carbon::now()->subMinutes(ContentLock::EXPIRES + 1),
                'updated_at' => Carbon::now()->subMinutes(ContentLock::EXPIRES + 1),
            ]);

        $this->assertCount(5, ContentLock::all());

        Artisan::call('cerpus:remove-content-locks', []);

        $this->assertCount(2, ContentLock::all());
    }
}
