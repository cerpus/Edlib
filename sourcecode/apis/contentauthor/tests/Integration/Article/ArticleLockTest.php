<?php

namespace Tests\Integration\Article;

use App\ApiModels\User;
use App\Article;
use App\ArticleCollaborator;
use App\ContentLock;
use Cerpus\VersionClient\VersionData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Helpers\MockAuthApi;
use Tests\Helpers\MockMQ;
use Tests\Helpers\MockResourceApi;
use Tests\Helpers\MockVersioningTrait;

class ArticleLockTest extends TestCase
{
    use RefreshDatabase, MockMQ, MockVersioningTrait, MockResourceApi, MockAuthApi;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $versionData = new VersionData();
        $this->setupVersion([
            'createVersion' => $versionData->populate((object) ['id' => $this->faker->uuid]),
        ]);
    }

    public function testArticleHasLockWhenUserEdits()
    {
        $this->withoutMiddleware();
        $this->setupAuthApi([
            'getUser' => new User("1", "aren", "aren", "none@none.com")
        ]);

        $authId = Str::uuid();
        $authName = $this->faker->name;
        $authEmail = $this->faker->email;
        $article = Article::factory()->create(['owner_id' => $authId]);

        $this->withSession(['authId' => $authId, 'email' => $authEmail, 'name' => $authName, 'verifiedEmails' => [$authEmail]])
            ->get(route('article.edit', $article->id));
        $this->assertDatabaseHas('content_locks', ['content_id' => $article->id, 'email' => $authEmail, 'name' => $authName]);

    }

    /**
     * @test
     */
    public function LockIsRemovedOnSave()
    {
        $this->setupAuthApi([
            'getUser' => new User("1", "aren", "aren", "none@none.com")
        ]);

        $authId = Str::uuid();
        $authName = $this->faker->name;
        $authEmail = $this->faker->email;
        $article = Article::factory()->create(['owner_id' => $authId]);

        $this->withSession([
            'authId' => $authId,
            'email' => $authEmail,
            'name' => $authName,
            'verifiedEmails' => [$authEmail]
        ])
            ->get(route('article.edit', $article->id));

        $this->assertDatabaseHas('content_locks', ['content_id' => $article->id, 'auth_id' => $authId]);

        $this->put(route('article.update', $article->id), [
            'title' => "NewTitle",
            'content' => '<div>Hello World!</div>',
        ]);

        $this->assertDatabaseMissing('content_locks', ['content_id' => $article->id]);
    }

    /**
     * @test
     */
    public function CanOnlyHaveOneLock()
    {
        $authId = Str::uuid();
        $authName = "John Doe";
        $authEmail = $this->faker->email;

        $article = Article::factory()->create(['owner_id' => $authId]);

        $this->setupAuthApi([
            'getUser' => new User("1", $authName, $authName, $authEmail)
        ]);

        $authId2 = Str::uuid();
        $authName2 = $this->faker->name;
        $authEmail2 = $this->faker->email;

        $articleCollaborator = ArticleCollaborator::factory()->make(['email' => $authEmail2]);
        $article->collaborators()->save($articleCollaborator);

        $this->withSession(['authId' => $authId, 'email' => $authEmail, 'name' => $authName, 'verifiedEmails' => [$authEmail]])
            ->get(route('article.edit', $article->id));

        $this->assertDatabaseHas('content_locks', ['content_id' => $article->id, 'auth_id' => $authId])
            ->assertCount(1, ContentLock::all());

        // Try to edit as another user
        $this->withSession(['authId' => $authId2, 'email' => $authEmail2, 'name' => $authName2, 'verifiedEmails' => [$authEmail2]])
            ->get(route('article.edit', $article->id))
            ->assertSee($authName);
        $this->assertCount(1, ContentLock::all());
    }

    /** @test */
    public function forkArticle_thenFail()
    {
        $this->setUpResourceApi();

        $authId = Str::uuid();

        $article = Article::factory()->create([
            'owner_id' => $authId,
            'license' => 'PRIVATE',
        ]);

        $authId2 = Str::uuid();
        $authName2 = $this->faker->name;
        $authEmail2 = $this->faker->email;

        // Try to fork as another user
        $this->withSession(['authId' => $authId2, 'email' => $authEmail2, 'name' => $authName2, 'verifiedEmails' => [$authEmail2]])
            ->get(route('article.edit', $article->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function forkArticle_thenSuccess()
    {
        $this->setUpResourceApi();
        $this->setupAuthApi([
            'getUser' => new User("1", "aren", "aren", "none@none.com")
        ]);

        $authId = Str::uuid();

        $article = Article::factory()->create(['owner_id' => $authId]);

        $authId2 = Str::uuid();
        $authName2 = $this->faker->name;
        $authEmail2 = $this->faker->email;

        // Try to fork as another user
        $this->withSession(['authId' => $authId2, 'email' => $authEmail2, 'name' => $authName2, 'verifiedEmails' => [$authEmail2]])
            ->get(route('article.edit', $article->id))
            ->assertSee($article->title);
    }
}
