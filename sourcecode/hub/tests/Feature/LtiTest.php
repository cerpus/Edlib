<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Lti\LtiLaunchBuilder;
use App\Lti\Oauth1\Oauth1Credentials;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LtiTest extends TestCase
{
    use RefreshDatabase;

    public function testReportsErrorsToToolConsumer(): void
    {
        $launchBuilder = $this->app->make(LtiLaunchBuilder::class);

        $request = $launchBuilder
            ->withClaim('launch_presentation_return_url', 'https://example.com/return')
            ->toPresentationLaunch(
                new Oauth1Credentials('h5p', 'secret2'),
                'http://localhost/lti/1.1/select',
                'some-resource-link',
            )
            ->getRequest();

        $this->post('/lti/1.1/select', $request->toArray())
            ->assertRedirect('https://example.com/return?lti_errorlog=Invalid+LTI+launch+type%2C+expected+%22ContentItemSelectionRequest%22+but+got+%22basic-lti-launch-request%22');
    }
}
