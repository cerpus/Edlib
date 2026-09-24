<?php

declare(strict_types=1);

namespace Tests\Feature\ContentAuthor;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiTool;
use App\Models\User;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

final class LeafVersionsTest extends TestCase
{
    use RefreshDatabase;

    private LtiTool $tool;
    private Credentials $credentials;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tool = LtiTool::factory()->create([
            'consumer_key' => 'test-key',
            'consumer_secret' => 'test-secret',
        ]);
        $this->credentials = new Credentials('test-key', 'test-secret');
    }

    /**
     * @param array<string, string> $params
     * @return \Illuminate\Testing\TestResponse<\Illuminate\Http\Response>
     */
    private function signedPost(string $url, array $params = []): \Illuminate\Testing\TestResponse
    {
        $oauthRequest = new Oauth1Request('POST', $url, $params);
        $oauthRequest = app(SignerInterface::class)->sign($oauthRequest, $this->credentials);

        return $this->post($url, $oauthRequest->toArray());
    }

    public function testReturnsEmptyWhenNoVersions(): void
    {
        $url = route('author.content.leaves', [$this->tool]);

        $this->signedPost($url, ['tag' => 'h5p:h5p.ndlathreeimage'])
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('data', 0));
    }

    public function testReturnsLeafVersions(): void
    {
        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->for($this->tool, 'tool')
                    ->withLaunchUrl('https://ca.test/h5p/1'),
            )
            ->create();

        $url = route('author.content.leaves', [$this->tool]);

        $this->signedPost($url)
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->count('data', 1)
                ->where('data.0.lti_launch_url', 'https://ca.test/h5p/1'));
    }

    public function testExcludesNonLeafVersions(): void
    {
        $content = Content::factory()->create();

        $parent = ContentVersion::factory()
            ->for($content)
            ->for($this->tool, 'tool')
            ->withLaunchUrl('https://ca.test/h5p/1')
            ->create();

        ContentVersion::factory()
            ->for($content)
            ->for($this->tool, 'tool')
            ->withLaunchUrl('https://ca.test/h5p/2')
            ->create(['previous_version_id' => $parent->id]);

        $url = route('author.content.leaves', [$this->tool]);

        $this->signedPost($url)
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->count('data', 1)
                ->where('data.0.lti_launch_url', 'https://ca.test/h5p/2'));
    }

    public function testFiltersByTag(): void
    {
        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->for($this->tool, 'tool')
                    ->withLaunchUrl('https://ca.test/h5p/1')
                    ->withTag('h5p:h5p.ndlathreeimage'),
            )
            ->create();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->for($this->tool, 'tool')
                    ->withLaunchUrl('https://ca.test/h5p/2')
                    ->withTag('h5p:h5p.escaperoom'),
            )
            ->create();

        $url = route('author.content.leaves', [$this->tool]);

        $this->signedPost($url, ['tag' => 'h5p:h5p.ndlathreeimage'])
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->count('data', 1)
                ->where('data.0.lti_launch_url', 'https://ca.test/h5p/1'));
    }

    public function testOnlyReturnsVersionsForThisTool(): void
    {
        $otherTool = LtiTool::factory()->create();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->for($this->tool, 'tool')
                    ->withLaunchUrl('https://ca.test/h5p/1'),
            )
            ->create();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->for($otherTool, 'tool')
                    ->withLaunchUrl('https://ca.test/h5p/2'),
            )
            ->create();

        $url = route('author.content.leaves', [$this->tool]);

        $this->signedPost($url)
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->count('data', 1)
                ->where('data.0.lti_launch_url', 'https://ca.test/h5p/1'));
    }

    public function testCopiedContentDoesNotAffectOriginalLeafStatus(): void
    {
        $originalContent = Content::factory()->create();
        $copyContent = Content::factory()->create();

        $originalVersion = ContentVersion::factory()
            ->for($originalContent)
            ->for($this->tool, 'tool')
            ->withLaunchUrl('https://ca.test/h5p/1')
            ->create();

        // Copy creates a version on a different content, pointing back to the original
        ContentVersion::factory()
            ->for($copyContent)
            ->for($this->tool, 'tool')
            ->withLaunchUrl('https://ca.test/h5p/2')
            ->create(['previous_version_id' => $originalVersion->id]);

        $url = route('author.content.leaves', [$this->tool]);

        $this->signedPost($url)
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('data', 2));
    }

    public function testRejectsInvalidCredentials(): void
    {
        $url = route('author.content.leaves', [$this->tool]);
        $badCredentials = new Credentials('test-key', 'wrong-secret');

        $oauthRequest = new Oauth1Request('POST', $url, []);
        $oauthRequest = app(SignerInterface::class)->sign($oauthRequest, $badCredentials);

        $this->post($url, $oauthRequest->toArray())
            ->assertUnauthorized();
    }

    public function testUpdateReleasesContentLock(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->for($this->tool, 'tool')
                    ->withLaunchUrl('https://ca.test/h5p/1'),
            )
            ->withUser($user)
            ->create();
        $version = $content->latestVersion;
        $this->assertNotNull($version);

        $content->acquireLock($user);
        $this->assertTrue($content->isLocked());

        $url = route('author.content.update', [$this->tool, $content, $version]);
        $contentItems = json_encode([
            '@context' => 'http://purl.imsglobal.org/ctx/lti/v1/ContentItem',
            '@graph' => [
                [
                    '@type' => 'LtiLinkItem',
                    'url' => 'https://ca.test/h5p/1',
                    'title' => 'Updated Title',
                    'mediaType' => 'application/vnd.ims.lti.v1.ltilink',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $this->signedPost($url, [
            'content_items' => $contentItems,
            'user_id' => (string) $user->id,
        ])->assertCreated();

        $this->assertFalse($content->refresh()->isLocked());
    }
}
