<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Article;
use App\ContentVersion;
use App\NdlaIdMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_isImported(): void
    {
        $parentArticle = Article::factory()->create();
        $article = Article::factory()->create([
            'parent_id' => $this->faker->uuid,
        ]);

        $parentVersion = ContentVersion::factory()->create([
            'id' => $parentArticle->version_id,
            'content_id' => $parentArticle->id,
        ]);
        $version = ContentVersion::factory()->create([
            'id' => $article->version_id,
            'content_id' => $article->id,
            'parent_id' => $parentVersion->id,
        ]);

        NdlaIdMapper::forceCreate([
            'ndla_id' => $this->faker->uuid,
            'ca_id' => $parentArticle->id,
            'type' => 'testing',
        ]);

        $isImported = $article->isImported();
        $this->assertTrue($isImported);
    }
}
