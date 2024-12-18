<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ContentRole;
use App\Jobs\PruneVersionlessContent;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiPlatform;
use App\Models\User;
use Carbon\Carbon;
use Cerpus\EdlibResourceKit\Oauth1\Request;
use Cerpus\EdlibResourceKit\Oauth1\Signer;
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
}
