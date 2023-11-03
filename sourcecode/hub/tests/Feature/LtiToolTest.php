<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LtiPlatform;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Cerpus\EdlibResourceKit\Oauth1\Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests suitability as an LTI tool.
 */
final class LtiToolTest extends TestCase
{
    use RefreshDatabase;

    private SignerInterface $oauthSigner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->oauthSigner = $this->app->make(SignerInterface::class);
    }

    public function testCookieCheckIsTransparentWhenCookiesAllowed(): void
    {
        $platform = LtiPlatform::factory()->create();

        $request = $this->oauthSigner->sign(
            new Request('POST', 'https://hub.edlib.test/lti/1.1/select', [
                'content_item_return_url' => 'http://example.com/',
                'lti_message_type' => 'ContentItemSelectionRequest',
            ]),
            $platform->getOauth1Credentials(),
        );

        // FIXME: would be nice if we could chain these, but Laravel doesn't
        // support following one redirect at a time.

        $this->post('/lti/1.1/select', $request->toArray())
            ->assertStatus(307)
            ->assertLocation('/lti/1.1/select?_edlib_cookie_check=1')
            ->assertCookie('_edlib_cookies');

        $this->withCookie('_edlib_cookies', '1')
            ->post('/lti/1.1/select?_edlib_cookie_check=1', $request->toArray())
            ->assertStatus(307)
            ->assertLocation('/lti/1.1/select');

        $this->post('/lti/1.1/select', $request->toArray())
            ->assertRedirect('https://hub.edlib.test/content');
    }

    public function testCookieCheckShowsCountermeasuresWhenCookiesNotAllowed(): void
    {
        $platform = LtiPlatform::factory()->create();

        $request = $this->oauthSigner->sign(
            new Request('POST', 'https://hub.edlib.test/lti/1.1/select', [
                'content_item_return_url' => 'http://example.com/',
                'lti_message_type' => 'ContentItemSelectionRequest',
            ]),
            $platform->getOauth1Credentials(),
        );

        $this->post('/lti/1.1/select', $request->toArray())
            ->assertStatus(307)
            ->assertLocation('/lti/1.1/select?_edlib_cookie_check=1')
            ->assertCookie('_edlib_cookies');

        $this->post('/lti/1.1/select?_edlib_cookie_check=1', $request->toArray())
            ->assertStatus(200)
            ->assertSee('Requesting storage access');
    }

    public function testAuthorizedItemSelectionRequestsRedirectToContentExplorer(): void
    {
        $platform = LtiPlatform::factory()->create();

        $request = $this->oauthSigner->sign(
            new Request('POST', 'https://hub.edlib.test/lti/1.1/select', [
                'content_item_return_url' => 'http://example.com/',
                'lti_message_type' => 'ContentItemSelectionRequest',
            ]),
            $platform->getOauth1Credentials(),
        );

        $this->withCookie('_edlib_cookies', '1')
            ->post('/lti/1.1/select', $request->toArray())
            ->assertRedirect('https://hub.edlib.test/content');
    }

    public function testUnauthorizedItemSelectionRequestsAreRejected(): void
    {
        $request = $this->oauthSigner->sign(
            new Request('POST', 'https://hub.edlib.test/lti/1.1/select', [
                'content_item_return_url' => 'http://example.com/',
                'lti_message_type' => 'ContentItemSelectionRequest',
            ]),
            new Credentials("it's a", "fake"),
        );

        $this->withCookie('_edlib_cookies', '1')
            ->post('/lti/1.1/select', $request->toArray())
            ->assertUnauthorized();
    }

    public function testReportsErrorsToToolConsumer(): void
    {
        $platform = LtiPlatform::factory()->create();

        $request = $this->oauthSigner->sign(
            new Request('POST', 'https://hub.edlib.test/lti/1.1/select', [
                'launch_presentation_return_url' => 'https://example.com/return',
                'lti_message_type' => 'basic-lti-launch-request',
            ]),
            $platform->getOauth1Credentials(),
        );

        $this->withCookie('_edlib_cookies', '1')
            ->post('/lti/1.1/select', $request->toArray())
            ->assertRedirect('https://example.com/return?lti_errorlog=Invalid+LTI+launch+type%2C+expected+%22ContentItemSelectionRequest%22+but+got+%22basic-lti-launch-request%22');
    }
}
