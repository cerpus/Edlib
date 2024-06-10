<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\PruneVersionlessContent;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiTool;
use Carbon\Carbon;
use DomainException;
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

    public function testPrunesVersionlessContent(): void
    {
        $toRemain1 = Content::factory()
            ->state(['updated_at' => now()])
            ->create();
        $toRemain2 = Content::factory()
            ->withPublishedVersion()
            ->state(['updated_at' => now()->subMonth()])
            ->create();
        $toBeDeleted = Content::factory()
            ->state(['updated_at' => now()->subMonth()])
            ->create();

        $this->command('schedule:test', [
            '--name' => PruneVersionlessContent::class,
        ])->assertOk();

        $this->assertModelExists($toRemain1);
        $this->assertModelExists($toRemain2);
        $this->assertModelMissing($toBeDeleted);
    }

    public function testGetsIdFromCreationDate(): void
    {
        $content = new Content();
        $content->created_at = new Carbon('@10');
        $content->save();

        $this->assertStringStartsWith('00000009rg', $content->id);
    }

    public function testGetsIdFromClockIfCreationDateUnset(): void
    {
        Carbon::setTestNow('@20');

        $content = new Content();
        $content->save();

        $this->assertStringStartsWith('0000000kh0', $content->id);
    }

    public function testGeneratesUnversionedDetailsUrlForPublishedContent(): void
    {
        $content = Content::factory()->withPublishedVersion()->create();
        $id = $content->id ?: $this->fail('Expected content ID');

        $this->assertSame(
            "https://hub-test.edlib.test/content/{$id}",
            $content->getDetailsUrl(),
        );
    }

    public function testGeneratesVersionedDetailsUrlForDraftContent(): void
    {
        $content = Content::factory()
            ->withVersion(ContentVersion::factory()->unpublished())
            ->create();
        $id = $content->id ?: $this->fail('Expected content ID');
        $versionId = $content->latestVersion?->id ?: $this->fail('Expected version ID');

        $this->assertSame(
            "https://hub-test.edlib.test/content/{$id}/version/{$versionId}",
            $content->getDetailsUrl(),
        );
    }

    public function testThrowsWhenGeneratingUrlToVersionlessContent(): void
    {
        $this->expectException(DomainException::class);

        (new Content())->getDetailsUrl();
    }
}
