<?php

declare(strict_types=1);

namespace Tests\Integration\Listeners\Article;

use App\Article;
use App\Content;
use App\ContentVersion;
use App\Events\ArticleWasSaved;
use App\Listeners\Article\HandleVersioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Tests\TestCase;

class HandleVersioningTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testHandle(): void
    {
        $article = Article::factory()->create();

        $event = new ArticleWasSaved(
            $article,
            new Request(),
            new Collection(),
            null,
            ContentVersion::PURPOSE_CREATE,
            [],
        );

        (new HandleVersioning())->handle($event);

        $article->refresh();

        $this->assertNotNull($article->version_id);
        $this->assertNull($article->parent_id);
        $this->assertNull($article->parent_version_id);

        $this->assertDatabaseCount('content_versions', 1);
        $this->assertDatabaseHas('content_versions', [
            'id' => $article->version_id,
            'content_id' => $article->id,
            'content_type' => Content::TYPE_ARTICLE,
            'parent_id' => null,
        ]);
    }

    /** Parent has version, but the new article does not have 'parent_version_id' set */
    public function testHandle_parentWithVersion_notConnected(): void
    {
        $parent = Article::factory()->create([
            'version_id' => $this->faker->uuid,
        ]);
        ContentVersion::factory()->create([
            'id' => $parent->version_id,
            'content_id' => $parent->id,
            'content_type' => Content::TYPE_ARTICLE,
        ]);
        $article = Article::factory()->create([
            'parent_id' => $parent->id,
            'parent_version_id' => null,
        ]);

        $event = new ArticleWasSaved(
            $article,
            new Request(),
            new Collection(),
            null,
            ContentVersion::PURPOSE_CREATE,
            [],
        );

        (new HandleVersioning())->handle($event);

        $parent->refresh();
        $article->refresh();

        $this->assertNotNull($parent->version_id);
        $this->assertNotNull($article->version_id);
        $this->assertNotNull($article->parent_version_id);

        $this->assertDatabaseCount('content_versions', 2);
        $this->assertDatabaseHas('content_versions', [
            'id' => $parent->version_id,
            'content_id' => $parent->id,
            'content_type' => Content::TYPE_ARTICLE,
            'parent_id' => null,
        ]);

        $this->assertDatabaseHas('content_versions', [
            'id' => $article->version_id,
            'content_id' => $article->id,
            'content_type' => Content::TYPE_ARTICLE,
            'parent_id' => $parent->version_id,
        ]);
    }

    /** Parent has version, and the new article has 'parent_version_id' set */
    public function testHandle_parentWithVersion_connected(): void
    {
        $parent = Article::factory()->create([
            'version_id' => $this->faker->uuid,
        ]);
        ContentVersion::factory()->create([
            'id' => $parent->version_id,
            'content_id' => $parent->id,
            'content_type' => Content::TYPE_ARTICLE,
        ]);
        $article = Article::factory()->create([
            'parent_id' => $parent->id,
            'parent_version_id' => $parent->version_id,
        ]);

        $event = new ArticleWasSaved(
            $article,
            new Request(),
            new Collection(),
            null,
            ContentVersion::PURPOSE_CREATE,
            [],
        );

        (new HandleVersioning())->handle($event);

        $parent->refresh();
        $article->refresh();

        $this->assertNotNull($article->version_id);

        $this->assertDatabaseCount('content_versions', 2);
        $this->assertDatabaseHas('content_versions', [
            'id' => $parent->version_id,
            'content_id' => $parent->id,
            'content_type' => Content::TYPE_ARTICLE,
            'parent_id' => null,
        ]);

        $this->assertDatabaseHas('content_versions', [
            'id' => $article->version_id,
            'content_id' => $article->id,
            'content_type' => Content::TYPE_ARTICLE,
            'parent_id' => $parent->version_id,
        ]);
    }

    /** The parent doesn't have a version */
    public function testHandle_parentWithoutVersion(): void
    {
        $parent = Article::factory()->create([
            'version_id' => null,
        ]);
        $article = Article::factory()->create([
            'parent_id' => $parent->id,
        ]);

        $event = new ArticleWasSaved(
            $article,
            new Request(),
            new Collection(),
            null,
            ContentVersion::PURPOSE_CREATE,
            [],
        );

        (new HandleVersioning())->handle($event);

        $parent->refresh();
        $article->refresh();

        $this->assertNotNull($parent->version_id);
        $this->assertNotNull($article->version_id);
        $this->assertNotNull($article->parent_version_id);

        $this->assertDatabaseCount('content_versions', 2);
        $this->assertDatabaseHas('content_versions', [
            'id' => $parent->version_id,
            'content_id' => $parent->id,
            'content_type' => Content::TYPE_ARTICLE,
            'parent_id' => null,
        ]);

        $this->assertDatabaseHas('content_versions', [
            'id' => $article->version_id,
            'content_id' => $article->id,
            'content_type' => Content::TYPE_ARTICLE,
            'parent_id' => $parent->version_id,
        ]);
    }
}
