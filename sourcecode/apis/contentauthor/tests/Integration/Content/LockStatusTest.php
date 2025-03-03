<?php

namespace Tests\Integration\Content;

use App\Article;
use App\ContentLock;
use App\ContentVersion;
use App\H5PContent;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class LockStatusTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testLockStatus()
    {
        $h5p = H5PContent::factory()->create();
        $lockStatus = ContentLock::factory()->create([
            'content_id' => $h5p->id,
            'updated_at' => Carbon::now()->subSeconds(30),
        ]);
        $user = User::factory()->make();

        $this->withSession(['authId' => $user->auth_id])
            ->get(route('lock.status', $lockStatus->content_id))
            ->assertJson([
                'isLocked' => true,
                'editUrl' => null,
            ]);

        $this->assertDatabaseCount('content_locks', 1);
    }

    public function testLockUpdate(): void
    {
        $user = User::factory()->make();
        $article = Article::factory()->create([
            'owner_id' => $user->auth_id,
            'version_id' => $this->faker->uuid,
        ]);

        $lock = ContentLock::factory()->create([
            'content_id' => $article->id,
            'auth_id' => $user->auth_id,
            'created_at' => Carbon::now()->subSeconds(30),
            'updated_at' => Carbon::now()->subSeconds(30),
        ]);

        $this->withSession(['authId' => $user->auth_id])
            ->post(route('lock.status', $lock->content_id))
            ->assertOk();

        $lockStatus = ContentLock::findOrFail($article->id);

        $this->assertTrue($lock->created_at->equalTo($lockStatus->created_at));
        $this->assertTrue($lock->updated_at->notEqualTo($lockStatus->updated_at));
        $this->assertTrue($lock->updated_at->isBefore($lockStatus->updated_at));
    }

    public function testLockStatusExpired()
    {
        $user = User::factory()->make();
        $originalArticle = Article::factory()->create([
            'id' => $this->faker->uuid,
            'owner_id' => $user->auth_id,
            'version_id' => $this->faker->uuid,
        ]);
        ContentVersion::factory()->create([
            'id' => $originalArticle->version_id,
            'content_id' => $originalArticle->id,
        ]);

        $latestArticle = Article::factory()->create([
            'id' => $this->faker->uuid,
            'version_id' => $this->faker->uuid,
            'owner_id' => $user->auth_id,
            'parent_id' => $originalArticle->id,
        ]);
        ContentVersion::factory()->create([
            'id' => $latestArticle->version_id,
            'content_id' => $latestArticle->id,
            'parent_id' => $originalArticle->version_id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);

        $lockStatus = ContentLock::factory()->create([
            'content_id' => $originalArticle->id,
            'updated_at' => Carbon::now()->subSeconds(91),
        ]);

        $this->withSession(['authId' => $user->auth_id])
            ->get(route('lock.status', $lockStatus->content_id))
            ->assertJson([
                'isLocked' => false,
                'editUrl' => route('article.edit', $latestArticle->id),
            ]);

        $this->assertDatabaseCount('content_locks', 1);
    }

    public function testLockStatusWithActivePulseButExpired()
    {
        config([
            'feature.lock-max-hours' => 20,
        ]);
        $user = User::factory()->make();
        $article = Article::factory()->create([
            'owner_id' => $user->auth_id,
            'version_id' => $this->faker->uuid,
        ]);

        $lock = ContentLock::factory()->create([
            'content_id' => $article->id,
            'auth_id' => $user->auth_id,
            'created_at' => Carbon::now()->subHours(24),
            'updated_at' => Carbon::now()->subSeconds(30),
        ]);

        // Locks created for more than 'feature.lock-max-hours' ago are not updated
        $this->withSession(['authId' => $user->auth_id])
            ->post(route('lock.status', $lock->content_id))
            ->assertOk();

        $lockStatus = ContentLock::findOrFail($article->id);

        $this->assertTrue($lock->created_at->equalTo($lockStatus->created_at));
        $this->assertTrue($lock->updated_at->equalTo($lockStatus->updated_at));
    }

    public function testYouNeedToBeLoggedIn()
    {
        $user = User::factory()->make();
        $originalArticle = Article::factory()->create(['owner_id' => $user->auth_id]);

        $lockStatus = ContentLock::factory()->create([
            'content_id' => $originalArticle->id,
            'updated_at' => Carbon::now()->subMinutes(40),
        ]);
        $this->get(route('lock.status', $lockStatus->content_id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
