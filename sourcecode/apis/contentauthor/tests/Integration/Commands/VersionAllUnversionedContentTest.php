<?php

namespace Tests\Integration\Commands;

use App\Article;
use App\Content;
use App\H5PContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class VersionAllUnversionedContentTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testArticles(): void
    {
        $unversioned = Article::factory(2)->create([
            'version_id' => null,
        ]);
        Article::factory(2)->create();

        $this->withoutMockingConsoleOutput()->artisan('cerpus:init-versioning');
        $result = Artisan::output();

        $unversioned->each(function (Article $article) use ($result) {
            $article->refresh();
            $this->assertStringContainsString(sprintf('Article: %s, %s, %s', $article->version_id, $article->title, $article->updated_at), $result);
            $this->assertDatabaseHas('content_versions', [
                'id' => $article->version_id,
                'content_id' => $article->id,
                'content_type' => Content::TYPE_ARTICLE,
                'created_at' => $article->updated_at->format('Y-m-d H:i:s.u'),
                'user_id' => $article->owner_id,
            ]);
        });
    }

    public function testH5Ps(): void
    {
        $unversioned = H5PContent::factory(2)->create();
        H5PContent::factory(2)->create([
            'version_id' => $this->faker->uuid,
        ]);

        $this->withoutMockingConsoleOutput()->artisan('cerpus:init-versioning');
        $result = Artisan::output();

        $unversioned->each(function (H5PContent $h5p) use ($result) {
            $h5p->refresh();
            $this->assertStringContainsString(sprintf('H5P: %s, %s, %s', $h5p->version_id, $h5p->title, $h5p->updated_at), $result);
            $this->assertDatabaseHas('content_versions', [
                'id' => $h5p->version_id,
                'content_id' => $h5p->id,
                'content_type' => Content::TYPE_H5P,
                'created_at' => $h5p->updated_at->format('Y-m-d H:i:s.u'),
                'user_id' => $h5p->user_id,
            ]);
        });
    }
}
