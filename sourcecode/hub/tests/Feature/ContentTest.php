<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiTool;
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

    public function testContentIsNotProxiedWhenProxyingDisabled(): void
    {
        $content = Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->tool(LtiTool::factory()->proxyLaunch(false))
                    ->state([
                        'lti_launch_url' => 'https://launch.example.com/'
                    ])
            )
            ->create();

        $this->assertSame(
            'https://launch.example.com/',
            $content->toLtiLinkItem()->getUrl(),
        );
    }

    public function testContentIsProxiedWhenProxyingEnabled(): void
    {
        $content = Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->state([
                        'lti_launch_url' => 'https://launch.example.com/'
                    ])
                    ->tool(LtiTool::factory()->proxyLaunch(true))
            )
            ->create();

        $this->assertSame(
            'https://hub-test.edlib.test/lti/content/' . $content->id,
            $content->toLtiLinkItem()->getUrl(),
        );
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
