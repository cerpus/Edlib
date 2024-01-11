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
            'updated_at' => Carbon::now()->subSeconds(30)
        ]);
        $user = User::factory()->make();

        $this->withSession(['authId' => $user->auth_id])
            ->get(route('lock.status', $lockStatus->content_id))
            ->assertJson([
                'isLocked' => true,
                'editUrl' => null
            ]);

        $this->assertDatabaseCount('content_locks', 1);
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
            'parent_id' => $originalArticle->id
        ]);
        ContentVersion::factory()->create([
            'id' => $latestArticle->version_id,
            'content_id' => $latestArticle->id,
            'parent_id' => $originalArticle->version_id,
        ]);

        $lockStatus = ContentLock::factory()->create([
            'content_id' => $originalArticle->id,
            'updated_at' => Carbon::now()->subSeconds(91)
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
            'feature.content-locking' => true,
            'feature.lock-max-hours' => 20,
        ]);
        $user = User::factory()->make();
        $originalArticle = Article::factory()->create(
            [
                'id' => '0800e3f5-d7a7-4add-a12a-16df86462837',
                'owner_id' => $user->auth_id,
                'version_id' => '7313f894-4dba-4ea4-9896-9da549e2e88f'
            ]
        );

        $lockStatus = ContentLock::factory()->create([
            'content_id' => $originalArticle->id,
            'auth_id' => $user->auth_id,
            'created_at' => Carbon::now()->subSeconds(30),
            'updated_at' => Carbon::now()->subSeconds(30),
        ]);

        $this->assertDatabaseHas('content_locks', [
            'content_id' => $originalArticle->id,
            'auth_id' => $user->auth_id,
        ]);

        $this->withSession(['authId' => $user->auth_id])
            ->post(route('lock.status', $lockStatus->content_id))
            ->assertOk();

        $lockStatus->refresh();
        $this->assertLessThan($lockStatus->updated_at, $lockStatus->created_at);

        $lastUpdated = $lockStatus->updated_at;
        $lockStatus->created_at = Carbon::now()->subDay();
        $lockStatus->save();

        $this->withSession(['authId' => $user->auth_id])
            ->post(route('lock.status', $lockStatus->content_id))
            ->assertOk();

        $lockStatus->refresh();

        $this->assertEquals($lastUpdated, $lockStatus->updated_at);
    }

    public function testYouNeedToBeLoggedIn()
    {
        $user = User::factory()->make();
        $originalArticle = Article::factory()->create(['owner_id' => $user->auth_id]);

        $lockStatus = ContentLock::factory()->create([
            'content_id' => $originalArticle->id,
            'updated_at' => Carbon::now()->subMinutes(40)
        ]);
        $this->get(route('lock.status', $lockStatus->content_id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
