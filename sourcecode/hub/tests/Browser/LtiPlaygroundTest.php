<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\LtiPlatform;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

final class LtiPlaygroundTest extends DuskTestCase
{
    public function testLtiPlaygroundIsVisibleInUserMenuWithDebugModeEnabled(): void
    {
        $user = User::factory()->name('Dee Bugg')->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/')
                ->press('Dee Bugg')
                ->assertDontSeeLink('LTI playground')
                ->clickLink('Preferences')
                ->check('debug_mode')
                ->press('Save')
                ->press('Dee Bugg')
                ->assertSeeLink('LTI playground')
                ->clickLink('LTI playground')
                ->assertTitleContains('LTI playground'),
        );
    }

    public function testLtiPlaygroundCanLaunchEdlibSampleContent(): void
    {
        $platform = LtiPlatform::factory()->create();
        $user = User::factory()->name('El Tee-Aye')->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/lti/playground')
                ->type('launch_url', 'https://hub-test.edlib.test/lti/samples/presentation')
                ->type('key', $platform->key)
                ->type('secret', $platform->secret)
                ->press('Launch')
                ->withinFrame(
                    'iframe',
                    fn(Browser $frame) => $frame
                        ->assertSee('If you can see this, the LTI launch was successful'),
                ),
        );
    }
}
