<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Lti\LtiLaunchBuilder;
use App\Models\LtiPlatform;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LtiTest extends TestCase
{
    use RefreshDatabase;

    private LtiLaunchBuilder $launchBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->launchBuilder = $this->app->make(LtiLaunchBuilder::class);
    }

    public function testCookieCheckIsTransparentWhenCookiesAllowed(): void
    {
        $platform = LtiPlatform::factory()->create();

        $request = $this->launchBuilder->toItemSelectionLaunch(
            $platform->getOauth1Credentials(),
            'https://hub.edlib.local/lti/1.1/select',
            'http://example.com/',
        )->getRequest();

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
            ->assertRedirect('https://hub.edlib.local/content');
    }

    public function testCookieCheckShowsCountermeasuresWhenCookiesNotAllowed(): void
    {
        $platform = LtiPlatform::factory()->create();

        $request = $this->launchBuilder->toItemSelectionLaunch(
            $platform->getOauth1Credentials(),
            'https://hub.edlib.local/lti/1.1/select',
            'http://example.com/',
        )->getRequest();

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

        $request = $this->launchBuilder->toItemSelectionLaunch(
            $platform->getOauth1Credentials(),
            'https://hub.edlib.local/lti/1.1/select',
            'http://example.com/',
        )->getRequest();

        $this->withCookie('_edlib_cookies', '1')
            ->post('/lti/1.1/select', $request->toArray())
            ->assertRedirect('https://hub.edlib.local/content');
    }

    public function testUnauthorizedItemSelectionRequestsAreRejected(): void
    {
        $request = $this->launchBuilder->toItemSelectionLaunch(
            new Credentials("it's a", "fake"),
            'https://hub.edlib.local/lti/1.1/select',
            'http://example.com/',
        )->getRequest();

        $this->withCookie('_edlib_cookies', '1')
            ->post('/lti/1.1/select', $request->toArray())
            ->assertUnauthorized();
    }

    public function testReportsErrorsToToolConsumer(): void
    {
        $platform = LtiPlatform::factory()->create();

        $request = $this->launchBuilder
            ->withClaim('launch_presentation_return_url', 'https://example.com/return')
            ->toPresentationLaunch(
                $platform->getOauth1Credentials(),
                'https://hub.edlib.local/lti/1.1/select',
                'some-resource-link',
            )
            ->getRequest();

        $this->withCookie('_edlib_cookies', '1')
            ->post('/lti/1.1/select', $request->toArray())
            ->assertRedirect('https://example.com/return?lti_errorlog=Invalid+LTI+launch+type%2C+expected+%22ContentItemSelectionRequest%22+but+got+%22basic-lti-launch-request%22');
    }
}
