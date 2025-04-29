<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ContentRole;
use App\Enums\ContentViewSource;
use App\Exceptions\ContentLockedException;
use App\Jobs\PruneVersionlessContent;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\ContentView;
use App\Models\ContentViewsAccumulated;
use App\Models\LtiPlatform;
use App\Models\User;
use Carbon\Carbon;
use Cerpus\EdlibResourceKit\Oauth1\Request;
use Cerpus\EdlibResourceKit\Oauth1\Signer;
use DateTimeImmutable;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
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

    public function testCanLaunchContentByEdlib2UsageId(): void
    {
        $url = 'https://hub-test.edlib.test/lti/content/by-edlib2-usage/a4e99aa5-a68c-4d26-9118-451fc05812b5';

        $credentials = LtiPlatform::factory()->create()->getOauth1Credentials();

        $content = Content::factory()
            ->withPublishedVersion()
            ->tag('edlib2_usage_id:a4e99aa5-a68c-4d26-9118-451fc05812b5')
            ->create();

        $parameters = $this->app->make(Signer::class)
            ->sign(new Request('POST', $url, [
                'lti_message_type' => 'basic-lti-launch-request',
                'lti_version' => 'LTI-1p0',
            ]), $credentials)
            ->toArray();

        $this
            ->withCookie('_edlib_cookies', '1')
            ->post($url, $parameters)
            ->assertRedirect('https://hub-test.edlib.test/content/' . $content->id . '/embed');
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

    #[TestDox('User with role $roleOfUser in content is granted $roleToCheck')]
    #[TestWith([ContentRole::Owner, ContentRole::Owner], 'owner, owner')]
    #[TestWith([ContentRole::Owner, ContentRole::Editor], 'owner, editor')]
    #[TestWith([ContentRole::Owner, ContentRole::Reader], 'owner, reader')]
    #[TestWith([ContentRole::Editor, ContentRole::Editor], 'editor, editor')]
    #[TestWith([ContentRole::Editor, ContentRole::Reader], 'editor, reader')]
    #[TestWith([ContentRole::Reader, ContentRole::Reader], 'reader, reader')]
    public function testCheckHasUserWithMinimumRole(ContentRole $roleOfUser, ContentRole $roleToCheck): void
    {
        $user = User::factory()
            ->create();
        $content = Content::factory()
            ->withUser($user, $roleOfUser)
            ->create();

        $this->assertTrue($content->hasUserWithMinimumRole($user, $roleToCheck));
    }

    #[TestDox('User with role $roleOfUser in content is denied $roleToCheck')]
    #[TestWith([ContentRole::Editor, ContentRole::Owner], 'editor, owner')]
    #[TestWith([ContentRole::Reader, ContentRole::Owner], 'reader, owner')]
    #[TestWith([ContentRole::Reader, ContentRole::Editor], 'reader, editor')]
    public function testUserWithRoleInContentDoesNotHaveMinimumRole(ContentRole $roleOfUser, ContentRole $roleToCheck): void
    {
        $user = User::factory()
            ->create();
        $content = Content::factory()
            ->withUser($user, $roleOfUser)
            ->create();

        $this->assertFalse($content->hasUserWithMinimumRole($user, $roleToCheck));
    }

    #[TestWith([ContentRole::Owner], 'owner')]
    #[TestWith([ContentRole::Editor], 'editor')]
    #[TestWith([ContentRole::Reader], 'reader')]
    public function testRolelessContentHasNeitherUserNorMinimumRole(ContentRole $roleToCheck): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create();

        $this->assertFalse($content->hasUser($user));
        $this->assertFalse($content->hasUserWithMinimumRole($user, $roleToCheck));
    }

    public function testsCountsViewsIncludingIndividualAndAccumulated(): void
    {
        $content = Content::factory()
            ->withView(ContentView::factory())
            ->withView(ContentView::factory())
            ->withViewsAccumulated(ContentViewsAccumulated::factory()->viewCount(3))
            ->withViewsAccumulated(ContentViewsAccumulated::factory()->viewCount(4))
            ->create();

        $this->assertSame(9, $content->countTotalViews());
    }

    public function testBuildsStatsGraph(): void
    {
        $content = Content::factory()
            ->withView(ContentView::factory()->createdAt(new DateTimeImmutable('2025-01-01 00:00:00 UTC'))->source(ContentViewSource::Detail))
            ->withView(ContentView::factory()->createdAt(new DateTimeImmutable('2025-01-02 00:00:00 UTC'))->source(ContentViewSource::Detail))
            ->withView(ContentView::factory()->createdAt(new DateTimeImmutable('2025-01-02 00:00:00 UTC'))->source(ContentViewSource::Detail))
            ->withView(ContentView::factory()->createdAt(new DateTimeImmutable('2025-01-02 00:00:00 UTC'))->source(ContentViewSource::Embed))
            ->withViewsAccumulated(ContentViewsAccumulated::factory()->dateAndHour('2025-01-01', 0)->source(ContentViewSource::Detail)->viewCount(3))
            ->withViewsAccumulated(ContentViewsAccumulated::factory()->dateAndHour('2025-01-02', 0)->source(ContentViewSource::Detail)->viewCount(5))
            ->withViewsAccumulated(ContentViewsAccumulated::factory()->dateAndHour('2025-01-02', 0)->source(ContentViewSource::Embed)->viewCount(2))
            ->create();

        $this->assertEquals([
            [
                'detail' => 4,
                'embed' => 0,
                'lti_platform' => 0,
                'standalone' => 0,
                'point' => '2025-01-01',
                'total' => 4,
            ],
            [
                'detail' => 7,
                'embed' => 3,
                'standalone' => 0,
                'lti_platform' => 0,
                'point' => '2025-01-02',
                'total' => 10,
            ],
        ], $content->buildStatsGraph(
            start: new DateTimeImmutable('2025-01-01 00:00:00 UTC'),
            end: new DateTimeImmutable('2025-02-01 00:00:00 UTC'),
        )->getData());
    }

    public function testAcquiresLock(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create();

        $this->assertFalse($content->isLocked());

        $content->acquireLock($user);

        $this->assertTrue($content->isLocked());
    }

    public function testCannotAcquireHeldLock(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create();
        $content->acquireLock($user);

        $this->expectException(ContentLockedException::class);

        $content->acquireLock($user);
    }

    public function testExpiredLockDoesNotCountAsLockHeld(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create();

        Carbon::setTestNow('2024-01-01T00:00:00Z');
        $content->acquireLock($user);
        $this->assertTrue($content->isLocked());

        Carbon::setTestNow('2025-01-01T00:00:00Z');
        $this->assertFalse($content->isLocked());
    }

    public function testCanAcquireLockWhenExpiredLockExists(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create();

        Carbon::setTestNow('2024-01-01T00:00:00Z');
        $content->acquireLock($user);

        Carbon::setTestNow('2025-01-01T00:00:00Z');
        $content->acquireLock($user);

        $this->assertTrue($content->isLocked());
    }

    public function testReleasesLock(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create();
        $content->acquireLock($user);

        $content->releaseLock($user);

        $this->assertFalse($content->isLocked());
    }

    public function testDoesNotReleaseLockHeldByAnotherUser(): void
    {
        $holder = User::factory()->create();
        $releaser = User::factory()->create();
        $content = Content::factory()->create();
        $content->acquireLock($holder);

        $content->releaseLock($releaser);

        $this->assertTrue($content->isLocked());
    }

    public function testRefreshesLock(): void
    {
        $content = Content::factory()->create();
        $user = User::factory()->create();

        Carbon::setTestNow('2025-01-01T00:00:00Z');
        $content->acquireLock($user);

        Carbon::setTestNow('2025-01-01T00:00:30Z');
        $content->refreshLock($user);

        $lock = $content->getActiveLock();
        $this->assertNotNull($lock);
        $this->assertSame('2025-01-01T00:00:00Z', $lock->created_at?->toIso8601ZuluString());
        $this->assertSame('2025-01-01T00:00:30Z', $lock->updated_at?->toIso8601ZuluString());
    }
}
