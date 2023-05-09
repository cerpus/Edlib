<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ContentTest extends TestCase
{
    use RefreshDatabase;

    public function testGetsLatestPublishedVersion(): void
    {
        $content = Content::factory()->create();
        $content->versions()->save(ContentVersion::factory()->unpublished()->create());
        $content->versions()->save(ContentVersion::factory()->published()->create());
        $content->versions()->save(ContentVersion::factory()->unpublished()->create());
        $content->versions()->save($expected = ContentVersion::factory()->published()->create());
        $content->versions()->save(ContentVersion::factory()->unpublished()->create());

        $this->assertTrue($expected->is($content->latestPublishedVersion));
    }

    public function testGetsLatestVersion(): void
    {
        $content = Content::factory()->create();
        $content->versions()->save(ContentVersion::factory()->create());
        $content->versions()->save(ContentVersion::factory()->create());
        $content->versions()->save($expected = ContentVersion::factory()->create());

        $this->assertTrue($expected->is($content->latestVersion));
    }

    public function testContentWithVersionsIsSearchable(): void
    {
        $content = Content::factory()->create();
        $content->versions()->save(ContentVersion::factory()->create());

        $this->assertTrue($content->shouldBeSearchable());
    }

    public function testContentWithoutVersionIsNotSearchable(): void
    {
        $content = Content::factory()->create();

        $this->assertFalse($content->shouldBeSearchable());
    }

    public function testCannotPreviewUnpublishedContent(): void
    {
        $content = Content::factory()
            ->has(ContentVersion::factory()->unpublished(), 'versions')
            ->create();

        $this->get("/content/{$content->id}")
            ->assertForbidden();
    }
}
