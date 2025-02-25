<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiPlatform;
use App\Models\LtiTool;
use App\Models\User;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\DuskTestCase;

final class EdlibLegacyTest extends DuskTestCase
{
    #[TestWith(['https://api.edlib.com/lti/v2/lti-links/eda9e9c4-d64a-4cb5-9f4a-c50d0f0e5f17'])]
    #[TestWith(['https://core.cerpus-course.com/lti/launch/eda9e9c4-d64a-4cb5-9f4a-c50d0f0e5f17'])]
    public function testCanLaunchLtiOnLegacyUrls(string $launchUrl): void
    {
        $externalPlatform = LtiPlatform::factory()->create();
        $internalPlatform = LtiPlatform::factory()->create();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->withLaunchUrl('https://hub-test.edlib.test/lti/samples/presentation')
                    ->tool(LtiTool::factory()->withCredentials($internalPlatform->getOauth1Credentials()))
                    ->published(),
            )
            ->tag('edlib2_usage_id:eda9e9c4-d64a-4cb5-9f4a-c50d0f0e5f17')
            ->create();

        $user = User::factory()->admin()->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('https://hub-test.edlib.test/lti/playground')
                ->type('launch_url', $launchUrl)
                ->type('key', $externalPlatform->key)
                ->type('secret', $externalPlatform->secret)
                ->type(
                    'parameters',
                    'lti_message_type=basic-lti-launch-request' .
                    "&lis_person_contact_email_primary={$user->email}",
                )
                ->press('Launch')
                ->withinFrame(
                    'iframe',
                    fn(Browser $frame) => $frame
                        ->assertSee('If you can see this, the LTI launch was successful'),
                ),
        );
    }
}
