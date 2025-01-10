<?php

namespace Tests\Integration\Article;

use App\Article;
use App\ArticleCollaborator;
use App\Content;
use App\ContentVersion;
use App\User;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ArticleVersioningTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testDatabaseVersioning()
    {
        $authId = Str::uuid();
        $article = Article::factory()->create(['owner_id' => $authId]);
        $startCount = Article::count();
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
            ->assertEquals($startCount + 1, Article::count()) // New version added
        ;
    }

    public function testVersioningTriggers()
    {
        $request = new Request();
        $authId = Str::uuid();
        $originalArticle = Article::factory()->create([
            'owner_id' => $authId,
            'license' => 'BY',
            'is_draft' => false,
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
                'collaborators' => 'c@d.com,a@b.com',
            ],
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
                'collaborators' => 'c@d.com,a@b.com',
            ],
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
                'collaborators' => 'c@d.com,a@b.com',
            ],
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
                'collaborators' => 'c@d.com,a@b.com',
            ],
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
                'collaborators' => 'c@d.com,a@b.com,e@f.com',
            ],
        );
        $this->assertFalse($originalArticle->requestShouldBecomeNewVersion($request));
    }

    #[DataProvider('provider_testVersioning')]
    public function testVersioning(bool $useLinearVersioning)
    {
        Config::set('feature.linear-versioning', $useLinearVersioning);

        $owner = User::factory()->make();
        $collaborator = User::factory()->make();
        $copyist = User::factory()->make();
        $eve = User::factory()->make();

        $article = Article::factory()->create([
            'owner_id' => $owner->auth_id,
            'license' => 'BY',
        ]);
        $version = ContentVersion::factory()->create([
            'content_id' => $article->id,
            'content_type' => Content::TYPE_ARTICLE,
            'user_id' => $owner->auth_id,
        ]);
        $article->version_id = $version->id;
        $article->save();

        $article->collaborators()->save(
            ArticleCollaborator::factory()->make(['email' => $collaborator->email]),
        );

        $article->fresh();

        $this->assertDatabaseCount('articles', 1);

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
        $this->assertDatabaseCount('articles', 2);
        $this->assertDatabaseHas('articles', [
            'title' => 'New title',
            'owner_id' => $owner->auth_id,
            'parent_id' => $article->id,
            'parent_version_id' => $article->version_id,
        ]);
        $this->assertDatabaseCount('content_versions', 2);
        $this->assertDatabaseHas('content_versions', [
            'parent_id' => $article->version_id,
            'content_type' => Content::TYPE_ARTICLE,
        ]);
        $secondArticle = Article::where('parent_id', $article->id)->first();

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

        $this->assertDatabaseCount('articles', 3);
        $this->assertDatabaseHas('content_versions', [
            'user_id' => $copyist->auth_id,
            'content_type' => Content::TYPE_ARTICLE,
            'version_purpose' => ContentVersion::PURPOSE_COPY,
            'parent_id' => $useLinearVersioning ? $secondArticle->version_id : $article->version_id,
        ]);
        $this->assertDatabaseHas('articles', [
            'title' => 'Another new title',
            'owner_id' => $copyist->auth_id,
            'parent_id' => $article->id,
        ]);
        $thirdArticle = Article::where('owner_id', $copyist->auth_id)
            ->where('title', 'Another new title')
            ->where('parent_version_id', $useLinearVersioning ? $secondArticle->version_id : $article->version_id)
            ->first();

        /** @var Article $copiedArticle */
        $copiedArticle = Article::where('owner_id', $copyist->auth_id)->first();
        $this->assertDatabaseMissing('article_collaborators', ['article_id' => $copiedArticle->id]);
        $this->assertDatabaseCount('article_collaborators', 2);
        $this->assertDatabaseCount('content_versions', 3);
        $this->assertDatabaseHas('content_versions', [
            'id' => $copiedArticle->version_id,
            'content_type' => Content::TYPE_ARTICLE,
        ]);

        $article->license = 'PRIVATE';
        $article->save();

        $this->assertDatabaseCount('article_collaborators', 2);

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

        $this->assertDatabaseCount('articles', 4);
        $this->assertDatabaseHas('articles', [
            'title' => 'Another new title',
            'owner_id' => $owner->auth_id,
            'parent_id' => $article->id,
        ]);
        $this->assertDatabaseHas('content_versions', [
            'parent_id' => $useLinearVersioning ? $secondArticle->version_id : $article->version_id,
            'content_type' => Content::TYPE_ARTICLE,
        ]);
        $this->assertDatabaseCount('article_collaborators', 3);
        $this->assertDatabaseCount('content_versions', 4);
    }

    public static function provider_testVersioning(): Generator
    {
        yield 'linear_versioning' => [true];
        yield 'non-linear_versioning' => [false];
    }
}
