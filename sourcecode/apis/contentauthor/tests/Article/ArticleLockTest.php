<?php

namespace Tests\Article;

use App\Article;
use Faker\Factory;
use Tests\TestCase;
use App\ContentLock;
use Tests\Traits\MockMQ;
use Illuminate\Support\Str;
use App\ArticleCollaborator;
use Illuminate\Http\Response;
use Tests\Traits\MockUserService;
use Tests\Traits\MockLicensingTrait;
use Tests\Traits\MockMetadataService;
use Tests\Traits\MockVersioningTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticleLockTest extends TestCase
{
    use RefreshDatabase, MockMetadataService, MockUserService, MockMQ, MockLicensingTrait, MockVersioningTrait;

    public function testArticleHasLockWhenUserEdits()
    {
        $this->withoutMiddleware();
        $this->setUpLicensing();
        $this->setUpVersion();
        $this->setupMetadataService([
            'getData' => null
        ]);
        $this->setupUserService([
            'getUser' => (object)[
                'identity' =>
                    (object)[
                        'firstName' => 'aren',
                        'lastName' => 'aren',
                        'email' => 'none@none.com',
                    ]
            ]
        ]);

        $faker = Factory::create();
        $authId = Str::uuid();
        $authName = $faker->name;
        $authEmail = $faker->email;
        $article = factory(Article::class)->create(['owner_id' => $authId]);

        $this->withSession(['authId' => $authId, 'email' => $authEmail, 'name' => $authName, 'verifiedEmails' => [$authEmail]])
            ->get(route('article.edit', $article->id));
        $this->assertDatabaseHas('content_locks', ['content_id' => $article->id, 'email' => $authEmail, 'name' => $authName]);

    }

    /**
     * @test
     */
    public function LockIsRemovedOnSave()
    {
        $this->setUpLicensing();
        $this->setUpVersion();
        $this->setupMetadataService([
            'getData' => true,
            'createData' => true
        ]);
        $this->setupUserService([
            'getUser' => (object)[
                'identity' =>
                    (object)[
                        'firstName' => 'aren',
                        'lastName' => 'aren',
                        'email' => 'none@none.com',
                    ]
            ]
        ]);

        $faker = Factory::create();
        $authId = Str::uuid();
        $authName = $faker->name;
        $authEmail = $faker->email;
        $article = factory(Article::class)->create(['owner_id' => $authId]);

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
        $this->setUpLicensing();
        $this->setUpVersion();
        $this->setupMetadataService([
            'getData' => true,
            'createData' => true
        ]);

        $faker = Factory::create();
        $authId = Str::uuid();
        $authName = "John Doe";
        $authEmail = $faker->email;

        $article = factory(Article::class)->create(['owner_id' => $authId]);

        $this->setupUserService([
            'getUser' => (object)[
                'identity' =>
                    (object)[
                        'firstName' => $authName,
                        'lastName' => $authName,
                        'email' => $authEmail,
                    ]
            ]
        ]);

        $authId2 = Str::uuid();
        $authName2 = $faker->name;
        $authEmail2 = $faker->email;

        $articleCollaborator = factory(ArticleCollaborator::class)->make(['email' => $authEmail2]);
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
        $this->setUpLicensing('PRIVATE', false);
        $this->setUpVersion();

        $faker = Factory::create();
        $authId = Str::uuid();

        $article = factory(Article::class)->create(['owner_id' => $authId]);

        $authId2 = Str::uuid();
        $authName2 = $faker->name;
        $authEmail2 = $faker->email;

        // Try to fork as another user
        $this->withSession(['authId' => $authId2, 'email' => $authEmail2, 'name' => $authName2, 'verifiedEmails' => [$authEmail2]])
            ->get(route('article.edit', $article->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function forkArticle_thenSuccess()
    {
        $this->setupMetadataService([
            'getData' => true,
            'createData' => true
        ]);
        $this->setupUserService([
            'getUser' => (object)[
                'identity' =>
                    (object)[
                        'firstName' => 'aren',
                        'lastName' => 'aren',
                        'email' => 'none@none.com',
                    ]
            ]
        ]);

        $this->setUpLicensing('PRIVATE', true);
        $this->setUpVersion();

        $faker = Factory::create();
        $authId = Str::uuid();

        $article = factory(Article::class)->create(['owner_id' => $authId]);

        $authId2 = Str::uuid();
        $authName2 = $faker->name;
        $authEmail2 = $faker->email;

        // Try to fork as another user
        $this->withSession(['authId' => $authId2, 'email' => $authEmail2, 'name' => $authName2, 'verifiedEmails' => [$authEmail2]])
            ->get(route('article.edit', $article->id))
            ->assertSee($article->title);
    }
}
