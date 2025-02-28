<?php

namespace Tests\Integration\Article;

use App\Article;
use App\ArticleCollaborator;
use App\ContentLock;
use App\Events\ArticleWasSaved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArticleLockTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testArticleHasLockWhenUserEdits()
    {
        $authId = Str::uuid();
        $authName = $this->faker->name;
        $authEmail = $this->faker->email;
        $article = Article::factory()->create(['owner_id' => $authId]);

        $this->withSession(['authId' => $authId, 'email' => $authEmail, 'name' => $authName, 'verifiedEmails' => [$authEmail]])
            ->get(route('article.edit', $article->id))
            ->assertOk();
        $this->assertDatabaseHas('content_locks', ['content_id' => $article->id, 'email' => $authEmail, 'name' => $authName]);
    }

    #[Test]
    public function LockIsRemovedOnSave()
    {
        Event::fake();

        $authId = Str::uuid();
        $authName = $this->faker->name;
        $authEmail = $this->faker->email;
        $article = Article::factory()->create(['owner_id' => $authId]);

        $this->withSession([
            'authId' => $authId,
            'email' => $authEmail,
            'name' => $authName,
            'verifiedEmails' => [$authEmail],
        ])
            ->get(route('article.edit', $article->id));

        $this->assertDatabaseHas('content_locks', ['content_id' => $article->id, 'auth_id' => $authId]);

        $this->put(route('article.update', $article->id), [
            'title' => "NewTitle",
            'content' => '<div>Hello World!</div>',
        ]);

        $this->assertDatabaseMissing('content_locks', ['content_id' => $article->id]);
        Event::assertDispatched(ArticleWasSaved::class);
    }

    #[Test]
    public function CanOnlyHaveOneLock()
    {
        $authId = Str::uuid();
        $authName = "John Doe";
        $authEmail = $this->faker->email;

        $article = Article::factory()->create(['owner_id' => $authId]);

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

    #[Test]
    public function forkArticle_thenSuccess()
    {
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
