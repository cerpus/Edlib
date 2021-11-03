<?php

namespace Tests\Article;

use App\User;
use App\Article;
use Tests\TestCase;
use Tests\Traits\MockMQ;
use Illuminate\Support\Str;
use App\ArticleCollaborator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\Traits\MockResourceApi;
use Tests\Traits\MockUserService;
use Tests\Traits\MockLicensingTrait;
use Tests\Traits\MockMetadataService;
use Tests\Traits\MockVersioningTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticleVersioningTest extends TestCase
{
    use RefreshDatabase, MockLicensingTrait, MockMetadataService, MockUserService, MockMQ, MockVersioningTrait, MockResourceApi;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testDatabaseVersioning()
    {
        $this->setUpLicensing('BY', true);
        $this->setupVersion();
        $this->setupMetadataService([
            'getData' => true,
            'createData' => true,
            'fetchAllCustomFields' => [],
        ]);
        $this->setupUserService([
            'getUser' => (object)[
                'identity' =>
                    (object)[
                        'firstName' => 'this',
                        'lastName' => 'that',
                        'email' => 'this@that.com',
                    ]
            ]
        ]);
        $authId = Str::uuid();
        $article = factory(Article::class)->create(['owner_id' => $authId]);
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
        $this->setUpLicensing('BY', true);
        $request = new Request();
        $authId = Str::uuid();
        $originalArticle = factory(Article::class)->create(['owner_id' => $authId]);
        $c1 = factory(ArticleCollaborator::class)->make(['email' => 'A@B.COM']);
        $c2 = factory(ArticleCollaborator::class)->make(['email' => 'c@d.com']);
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
        $this->setupVersion();
        $this->setUpLicensing('BY', true);
        $this->setupMetadataService([
            'getData' => true,
            'createData' => true,
            'fetchAllCustomFields' => [],
        ]);
        $this->setupUserService([
            'getUser' => (object)[
                'identity' =>
                    (object)[
                        'firstName' => 'this',
                        'lastName' => 'that',
                        'email' => 'this@that.com',
                    ]
            ]
        ]);
        $owner = factory(User::class)->make();
        $collaborator = factory(User::class)->make();
        $copyist = factory(User::class)->make();
        $eve = factory(User::class)->make();

        $article = factory(Article::class)->create(['owner_id' => $owner->auth_id]);
        $article->collaborators()->save(factory(ArticleCollaborator::class)->create(['email' => $collaborator->email]));

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
        $copiedArticle = Article::where('owner_id', $copyist->auth_id)->first();
        $this->assertDatabaseMissing('article_collaborators', ['article_id' => $copiedArticle->id]);
        $this->assertCount(2, ArticleCollaborator::all());


        $this->setUpLicensing('PRIVATE', false);

        // Cannot edit article if not collaborator and non copyable resource
        $this->withSession([
            'authId' => $eve->auth_id,
            'email' => $eve->email,
            'verifiedEmails' => [$eve->email],


        ])
            ->get(route('article.edit', $article->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertCount(2, ArticleCollaborator::all());

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
                'collaborators' => 'a@b.com,c@d.com',
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
        $this->assertCount(3, ArticleCollaborator::all());
    }
}
