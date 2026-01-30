<?php

namespace Tests\Integration\Article;

use App\Article;
use App\Libraries\Versioning\VersionableObject;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
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

        /*
         * Nothing has changed -> no new version
         */
        $request->request->add(
            [
                'title' => $originalArticle->title,
                'content' => $originalArticle->content,
                'license' => 'BY',
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

    public function testVersioning()
    {
        $owner = User::factory()->make();
        $collaborator = User::factory()->make();
        $copyist = User::factory()->make();

        $article = Article::factory()->create([
            'owner_id' => $owner->auth_id,
            'license' => 'BY',
        ]);
        $article->save();

        $article->fresh();

        $this->assertDatabaseCount('articles', 1);

        $this->withSession([
            'authId' => $collaborator->auth_id,
        ])
            ->get(route('article.edit', $article->id))
            ->assertStatus(Response::HTTP_OK);

        $this->put(route('article.update', $article->id), [
            'title' => "New title",
            'content' => $article->content,
        ])
            ->assertCreated();
        $this->assertDatabaseCount('articles', 2);
        $this->assertDatabaseHas('articles', [
            'title' => 'New title',
            'owner_id' => $collaborator->auth_id,
            'parent_id' => $article->id,
            'version_purpose' => VersionableObject::PURPOSE_COPY,
        ]);

        $this->withSession([
            'authId' => $copyist->auth_id,
        ])
            ->get(route('article.edit', $article->id))
            ->assertStatus(Response::HTTP_OK);

        $this->put(route('article.update', $article->id), [
            'title' => "Another new title",
            'content' => $article->content,
        ]);

        $this->assertDatabaseCount('articles', 3);
        $this->assertDatabaseHas('articles', [
            'title' => 'Another new title',
            'owner_id' => $copyist->auth_id,
            'parent_id' => $article->id,
            'version_purpose' => VersionableObject::PURPOSE_COPY,
        ]);
    }
}
