<?php

namespace Tests\Integration\Article;

use App\Article;
use App\ArticleCollaborator;
use App\User;
use Cerpus\VersionClient\VersionData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Helpers\MockAuthApi;
use Tests\Helpers\MockMQ;
use Tests\Helpers\MockResourceApi;
use Tests\Helpers\MockVersioningTrait;

class ArticleVersioningTest extends TestCase
{
    use RefreshDatabase;
    use MockMQ;
    use MockVersioningTrait;
    use MockResourceApi;
    use MockAuthApi;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $versionData = new VersionData();
        $this->setupVersion([
            'createVersion' => $versionData->populate((object) ['id' => $this->faker->uuid]),
            'getVersion' => $versionData->populate((object) ['id' => $this->faker->uuid]),
        ]);
    }

    public function testDatabaseVersioning()
    {
        $this->setupAuthApi([
            'getUser' => new \App\ApiModels\User("1", "this", "that", "this@that.com")
        ]);
        $authId = Str::uuid();
        $article = Article::factory()->create(['owner_id' => $authId]);
        $startCount = Article::all()->count();
        $this->withSession(['authId' => $authId])
            ->put(route('article.update', $article->id), [
                'title' => 'Title',
                'content' => 'Content',
            ]);
        $this->assertDatabaseHas('articles', [ // See the new
            'title' => 'Title',
            'content' => 'Content',
        ])
            ->assertDatabaseHas('articles', [ // See the old
                'title' => $article->title,
                'content' => $article->content,
            ])
            ->assertEquals($startCount + 1, Article::all()->count()) // New version added
        ;
    }

    public function testVersioningTriggers()
    {
        $request = new Request();
        $authId = Str::uuid();
        $originalArticle = Article::factory()->create([
            'owner_id' => $authId,
            'license' => 'BY',
            'is_draft' => false
        ]);
        $c1 = ArticleCollaborator::factory()->make(['email' => 'A@B.COM']);
        $c2 = ArticleCollaborator::factory()->make(['email' => 'c@d.com']);
        $originalArticle->collaborators()->save($c1);
        $originalArticle->collaborators()->save($c2);

        /*
         * Nothing has changed -> no new version
         */
        $request->request->add(
            [
                'title' => $originalArticle->title,
                'content' => $originalArticle->content,
                'license' => 'BY',
                'collaborators' => 'c@d.com,a@b.com'
            ]
        );
        $this->assertFalse($originalArticle->requestShouldBecomeNewVersion($request));

        /*
         * Title changed -> new article
         */
        $request->request->add(
            [
                'title' => $originalArticle->title . '1',
                'content' => $originalArticle->content,
                'license' => 'BY',
                'collaborators' => 'c@d.com,a@b.com'
            ]
        );
        $this->assertTrue($originalArticle->requestShouldBecomeNewVersion($request));

        /*
         * Content changed -> new article
         */
        $request->request->add(
            [
                'title' => $originalArticle->title,
                'content' => $originalArticle->content . '1',
                'license' => 'BY',
                'collaborators' => 'c@d.com,a@b.com'
            ]
        );
        $this->assertTrue($originalArticle->requestShouldBecomeNewVersion($request));

        /*
         * License changed -> new version
         */
        $request->request->add(
            [
                'title' => $originalArticle->title,
                'content' => $originalArticle->content,
                'license' => 'BY-ND',
                'collaborators' => 'c@d.com,a@b.com'
            ]
        );
        $this->assertTrue($originalArticle->requestShouldBecomeNewVersion($request));

        /*
         * New collaborator -> no new  version
         */
        $request->request->add(
            [
                'title' => $originalArticle->title,
                'content' => $originalArticle->content,
                'license' => 'BY',
                'collaborators' => 'c@d.com,a@b.com,e@f.com'
            ]
        );
        $this->assertFalse($originalArticle->requestShouldBecomeNewVersion($request));
    }

    public function testVersioning()
    {
        $this->setUpResourceApi();
        $this->setupAuthApi([
            'getUser' => new \App\ApiModels\User("1", "this", "that", "this@that.com")
        ]);
        $owner = User::factory()->make();
        $collaborator = User::factory()->make();
        $copyist = User::factory()->make();
        $eve = User::factory()->make();

        $article = Article::factory()->create([
            'owner_id' => $owner->auth_id,
            'license' => 'BY',
        ]);
        $article->collaborators()->save(ArticleCollaborator::factory()->create(['email' => $collaborator->email]));

        $article->fresh();

        $this->assertCount(1, Article::all());

        $this->withSession([
            'authId' => $collaborator->auth_id,
            'email' => $collaborator->email,
            'verifiedEmails' => [$collaborator->email],
        ])
        ->get(route('article.edit', $article->id))
        ->assertStatus(Response::HTTP_OK);

        $this->put(route('article.update', $article->id), [
            'title' => "New title",
            'content' => $article->content,
        ]);
        $this->assertCount(2, Article::all());
        $this->assertDatabaseHas('articles', ['title' => 'New title', 'owner_id' => $owner->auth_id]);


        $this->withSession([
            'authId' => $copyist->auth_id,
            'email' => $copyist->email,
            'verifiedEmails' => [$copyist->email],
        ])
        ->get(route('article.edit', $article->id))
        ->assertStatus(Response::HTTP_OK);

        $this->put(route('article.update', $article->id), [
            'title' => "Another new title",
            'content' => $article->content,
        ]);

        $this->assertCount(3, Article::all());
        $this->assertDatabaseHas('articles', ['title' => 'Another new title', 'owner_id' => $copyist->auth_id]);

        $article->license = 'PRIVATE';
        $article->save();

        // Cannot edit article if not collaborator and non copyable resource
        $this->withSession([
            'authId' => $eve->auth_id,
            'email' => $eve->email,
            'verifiedEmails' => [$eve->email],
        ])
        ->get(route('article.edit', $article->id))
        ->assertStatus(Response::HTTP_FORBIDDEN);

        // Well, maybe if i post directly? PURE GENIUS!
        $this->withSession([
            'authId' => $eve->auth_id,
            'email' => $eve->email,
            'verifiedEmails' => [$eve->email],
        ])
        ->put(route('article.update', $article->id), [
            '_token' => csrf_token(),
            'title' => 'Evil edit',
            'content' => 'Muahahaha',
            'license' => 'BY',
        ])
        ->assertStatus(Response::HTTP_FORBIDDEN);

        // Can edit if you are a collaborator even if the resource is non copyable
        $this->withSession([
            'authId' => $collaborator->auth_id,
            'email' => $collaborator->email,
            'verifiedEmails' => [$collaborator->email],
        ])
        ->get(route('article.edit', $article->id))
        ->assertStatus(Response::HTTP_OK);

        $this->put(route('article.update', $article->id), [
            'title' => "Another new title",
            'content' => $article->content,
        ]);

        $this->assertCount(4, Article::all());
        $this->assertDatabaseHas('articles', ['title' => 'Another new title', 'owner_id' => $owner->auth_id]);
    }
}
