<?php

namespace Tests\Integration\Models;

use App\ContentLock;
use App\H5PContent;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ContentLockTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testLock(): void
    {
        $user = User::factory()->make();
        $this->session([
            'authId' => $user->auth_id,
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $content = H5PContent::factory()->create([
            'user_id' => $user->auth_id,
        ]);

        $this->assertDatabaseEmpty('content_locks');
        (new ContentLock())->lock($content->id);

        $this->assertDatabaseHas('content_locks', [
            'content_id' => $content->id,
            'auth_id' => $user->auth_id,
            'email' => $user->email,
            'name' => $user->name,
        ]);
    }

    public function testUnlock(): void
    {
        $user = User::factory()->make();
        $content = H5PContent::factory()->create([
            'user_id' => $user->auth_id,
        ]);
        ContentLock::factory()->create([
            'content_id' => $content->id,
            'auth_id' => $user->auth_id,
            'email' => $user->email,
            'name' => $user->name,
        ]);
        $this->assertDatabaseHas('content_locks', [
            'content_id' => $content->id,
            'auth_id' => $user->auth_id,
            'email' => $user->email,
            'name' => $user->name,
        ]);

        (new ContentLock())->unlock($content->id);

        $this->assertDatabaseEmpty('content_locks');
    }

    #[TestWith([true], 'Current user has active lock')]
    #[TestWith([false], 'Other user has active lock')]
    public function testHasLock(bool $asLockOwner): void
    {
        $lockOwner = User::factory()->make();
        $user = $asLockOwner ? $lockOwner : User::factory()->make();

        $this->session([
            'authId' => $user->auth_id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
        $content = H5PContent::factory()->create();

        $this->assertNull((new ContentLock())->hasLock($content->id));

        ContentLock::factory()->create([
            'content_id' => $content->id,
            'auth_id' => $lockOwner->auth_id,
            'email' => $lockOwner->email,
            'name' => $lockOwner->name,
        ]);

        $activeLock = (new ContentLock())->hasLock($content->id);

        if ($asLockOwner) {
            $this->assertFalse($activeLock);
        } else {
            $this->assertInstanceOf(ContentLock::class, $activeLock);
            $this->assertSame($lockOwner->auth_id, $activeLock->auth_id);
        }
    }
}
