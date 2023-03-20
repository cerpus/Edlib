<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ExplorerTest extends TestCase
{
    use RefreshDatabase;

    public function testExploresContent(): void
    {
        $content = Content::factory()
            ->has(ContentVersion::factory(), 'versions')
            ->create();

        $this->assertIsString($content->latestVersion?->resource?->title);

        $this->get('/content-explorer')
            ->assertOk()
            ->assertSee($content->latestVersion->resource->title);
    }
}
