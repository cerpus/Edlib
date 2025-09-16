<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Article;
use App\NdlaIdMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testArticleWithMapperIsImported(): void
    {
        $article = Article::factory()->create();
        NdlaIdMapper::forceCreate([
            'ndla_id' => $this->faker->uuid,
            'ca_id' => $article->id,
            'type' => 'testing',
        ]);

        $this->assertTrue($article->isImported());
    }

    public function testInheritsImportStatus(): void
    {
        $parentArticle = Article::factory()->create();
        $article = Article::factory()->create([
            'parent_id' => $parentArticle->id,
        ]);
        NdlaIdMapper::forceCreate([
            'ndla_id' => $this->faker->uuid,
            'ca_id' => $parentArticle->id,
            'type' => 'testing',
        ]);

        $this->assertTrue($article->isImported());
    }

    public function testArticleWithoutMapperIsNotImported(): void
    {
        $article = Article::factory()->create();

        $this->assertFalse($article->isImported());
    }
}
