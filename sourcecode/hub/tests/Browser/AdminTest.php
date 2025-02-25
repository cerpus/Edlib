<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\Context;
use App\Models\LtiPlatform;
use App\Models\LtiTool;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\LtiPlatformAddedAlert;
use Tests\Browser\Components\LtiPlatformCard;
use Tests\Browser\Components\LtiToolCard;
use Tests\DuskTestCase;

final class AdminTest extends DuskTestCase
{
    public function testCanRebuildIndex(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->admin()->create();

            $browser
                ->loginAs($user->email)
                ->visit('/admin')
                ->press('Rebuild content index')
                ->waitFor('#htmxConfirmModal')
                ->with(
                    '#htmxConfirmModal',
                    fn(Browser $modal) => $modal
                        ->assertSee('Are you sure you want to continue?')
                        ->press('OK'),
                )
                ->waitForReload()
                ->assertPathIs('/admin')
                ->assertSee('Rebuilding content index…');
        });
    }

    public function testCanRemoveUnusedLtiTools(): void
    {
        $this->browse(function (Browser $browser) {
            LtiTool::factory()->withName('Unused tool')->create();
            $user = User::factory()->admin()->create();

            $browser
                ->loginAs($user->email)
                ->visit('/admin/lti-tools')
                ->with('main', function (Browser $main) {
                    $main
                        ->assertSee('Unused tool')
                        ->press('Remove');
                })
                ->assertSee('The LTI tool "Unused tool" was removed');
        });
    }

    public function testCanRemoveLtiPlatforms(): void
    {
        LtiPlatform::factory()->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs(User::factory()->admin()->create()->email)
                ->visit('/admin/lti-platforms')
                ->resize(1920, 1920)
                ->with(
                    'main .lti-platform-card',
                    fn(Browser $main) => $main
                        ->press('Remove'),
                )
                ->waitFor('#htmxConfirmModal')
                ->with(
                    '#htmxConfirmModal',
                    fn(Browser $modal) => $modal
                        ->assertSee('Are you sure you want to remove the LTI platform?')
                        ->press('OK'),
                )
                ->waitForReload()
                ->assertNotPresent('.lti-platform-card')
                ->assertSee('The LTI platform has been removed'),
        );
    }

    public function testCanAddLtiPlatforms(): void
    {
        $user = User::factory()->admin()->name('Admin User')->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/')
                ->press('Admin User')
                ->clickLink('Admin home')
                ->clickLink('Manage LTI platforms')
                ->type('name', 'My LTI platform')
                ->check('enable_sso')
                ->check('authorizes_edit')
                ->press('Create')
                ->with(new LtiPlatformAddedAlert(), function (Browser $alert) {
                    $alert->assertSee('LTI platform "My LTI platform" was successfully created.');

                    $this->assertDatabaseHas(LtiPlatform::class, [
                        'key' => $alert->text('@key'),
                        'secret' => $alert->text('@secret'),
                        'authorizes_edit' => true,
                        'enable_sso' => true,
                    ]);
                })
                ->with(
                    new LtiPlatformCard(),
                    fn(Browser $card) => $card
                        ->assertSeeIn('@enable-sso', 'Yes')
                        ->assertSeeIn('@authorizes-edit', 'Yes'),
                ),
        );
    }

    public function testCanEditLtiPlatform(): void
    {
        LtiPlatform::factory()->name('Old name')->create();
        $user = User::factory()->admin()->name('Admin User')->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/')
                ->press('Admin User')
                ->clickLink('Admin home')
                ->clickLink('Manage LTI platforms')
                ->clickLink('Edit')
                ->type('name', 'New name')
                ->uncheck('input[name="enable_sso"]')
                ->press('Update')
                ->assertSee('LTI platform updated.')
                ->within(
                    new LtiPlatformCard(),
                    fn(Browser $card) => $card
                        ->assertSeeIn('@title', 'New name')
                        ->assertSeeIn('@enable-sso', 'No'),
                ),
        );
    }

    public function testCreatesAndUsesAdminEndpoints(): void
    {
        $platform = LtiPlatform::factory()
            ->create();

        $tool = LtiTool::factory()
            ->withCredentials($platform->getOauth1Credentials())
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs(User::factory()->admin()->create()->email)
                ->assertAuthenticated()
                ->visit('/admin/lti-tools/' . $tool->id . '/extras/add')
                ->type('name', 'LTI extra test')
                ->type('lti_launch_url', 'https://hub-test.edlib.test/lti/samples/resize')
                ->check('admin')
                ->press('Add')
                ->assertSee('The extra endpoint was added')
                ->visit('/admin')
                ->clickLink('LTI extra test')
                ->withinFrame(
                    '.lti-launch',
                    fn(Browser $frame) => $frame
                        ->press('Resize to 640'),
                ),
        );
    }

    public function testCreatesToolsWithSlug(): void
    {
        $platform = LtiPlatform::factory()->create();
        $user = User::factory()->admin()->create();

        $this->browse(
            fn(Browser $browser) =>
            $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/')
                ->press($user->name)
                ->clickLink('Admin home')
                ->clickLink('Manage LTI tools')
                ->clickLink('Add LTI tool')
                ->type('name', 'The tool')
                ->type('slug', 'the-tool')
                ->type('creator_launch_url', 'https://hub-test.edlib.test/lti/samples/resize')
                ->type('consumer_key', $platform->key)
                ->type('consumer_secret', $platform->secret)
                ->press('Add')
                ->assertSee('LTI tool added')
                ->clickLink('Create')
                ->assertUrlIs('https://hub-test.edlib.test/content/create/the-tool')
                ->assertPresent('.lti-launch'),
        );
    }

    public function testCanEditUrlSlugForTool(): void
    {
        LtiTool::factory()->withName('Hammer')->slug('the-old-slug')->create();
        $user = User::factory()->name('Ben Hammerhead')->admin()->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/')
                ->press('Ben Hammerhead')
                ->clickLink('Admin home')
                ->clickLink('Manage LTI tools')
                ->clickLink('Edit')
                ->assertValue('input[name="slug"]', 'the-old-slug')
                ->type('slug', 'the-new-slug')
                ->press('Update')
                ->assertSee('LTI tool updated.')
                ->visit('/content/create/the-new-slug')
                ->assertPresent('.lti-launch'),
        );
    }

    public function testCanEditFlagsForTool(): void
    {
        $user = User::factory()->name('Flagg Stang')->admin()->create();
        LtiTool::factory()
            ->withName('The Tool')
            ->sendName()
            ->sendEmail()
            ->create();

        $this->browse(
            fn(Browser $browser) =>
            $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/')
                ->press('Flagg Stang')
                ->clickLink('Admin home')
                ->clickLink('Manage LTI tools')
                ->with(
                    new LtiToolCard(),
                    fn(Browser $card) =>
                    $card
                        ->assertSeeIn('@send-email', 'Yes')
                        ->assertSeeIn('@send-name', 'Yes'),
                )
                ->clickLink('Edit')
                ->assertChecked('send_name')
                ->uncheck('send_name')
                ->assertChecked('send_email')
                ->uncheck('send_email')
                ->press('Update')
                ->assertSee('LTI tool updated.')
                ->press('Flagg Stang')
                ->clickLink('Admin home')
                ->clickLink('Manage LTI tools')
                ->with(
                    new LtiToolCard(),
                    fn(Browser $card) =>
                    $card
                        ->assertSeeIn('@send-email', 'No')
                        ->assertSeeIn('@send-name', 'No'),
                ),
        );
    }

    public function testCanAddContextToLtiPlatform(): void
    {
        LtiPlatform::factory()->create();
        $context = Context::factory()->name('ndla_users')->create();
        $user = User::factory()->admin()->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/admin')
                ->clickLink('Manage LTI platforms')
                ->within(
                    new LtiPlatformCard(),
                    fn(Browser $card) => $card
                        ->assertSeeIn('@context-count', '0')
                        ->clickLink('Contexts'),
                )
            // Dusk does not support selecting by the choice's label
                ->select('context', $context->id)
                ->press('Add')
                ->assertSee('The context was added to the LTI platform')
                ->visit('/admin')
                ->clickLink('Manage LTI platforms')
                ->within(
                    new LtiPlatformCard(),
                    fn(Browser $card) => $card
                        ->assertSeeIn('@context-count', '1'),
                ),
        );
    }

    public function testListsAdmins(): void
    {
        User::factory()->withEmail('admin@edlib.test')->admin()->create();
        User::factory()->withEmail('nimda@bilde.test')->admin()->create();
        User::factory()->withEmail('luser@example.com')->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs('admin@edlib.test')
                ->assertAuthenticated()
                ->visit('/admin/admins')
                ->with(
                    'main table',
                    fn(Browser $table) => $table
                        ->assertSee('admin@edlib.test')
                        ->assertSee('nimda@bilde.test')
                        ->assertDontSee('luser@example.com'),
                ),
        );
    }

    public function testAddsAdmins(): void
    {
        User::factory()->withEmail('admin@edlib.test')->admin()->create();
        User::factory()->withEmail('nimda@bilde.test')->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs('admin@edlib.test')
                ->assertAuthenticated()
                ->visit('/admin/admins')
                ->assertDontSeeIn('main table', 'nimda@bilde.test')
                ->type('email', 'nimda@bilde.test')
                ->press('Add')
                ->assertSeeIn('main table', 'nimda@bilde.test'),
        );
    }

    public function testEmailOfAddedAdminMustBelongToExistingUser(): void
    {
        User::factory()->withEmail('admin@edlib.test')->admin()->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs('admin@edlib.test')
                ->assertAuthenticated()
                ->visit('/admin/admins')
                ->type('email', 'nimda@bilde.test')
                ->press('Add')
                ->assertDontSeeIn('main table', 'nimda@bilde.test')
                ->assertSeeIn('.invalid-feedback', 'No user with that email address'),
        );
    }

    public function testEmailOfAddedAdminMustBeVerified(): void
    {
        User::factory()->withEmail('admin@edlib.test')->admin()->create();
        User::factory()->withEmail('nimda@bilde.test', verified: false)->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs('admin@edlib.test')
                ->assertAuthenticated()
                ->visit('/admin/admins')
                ->type('email', 'nimda@bilde.test')
                ->press('Add')
                ->assertDontSeeIn('main table', 'nimda@bilde.test')
                ->assertSeeIn('.invalid-feedback', 'User does not have a verified email address'),
        );
    }

    public function testRemovesAdmins(): void
    {
        User::factory()->withEmail('admin@edlib.test')->admin()->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs('admin@edlib.test')
                ->assertAuthenticated()
                ->visit('/admin/admins')
                ->with(
                    'main table',
                    fn(Browser $table) => $table
                        ->assertSee('admin@edlib.test')
                        ->press('Remove'),
                )
                ->assertTitleContains('Forbidden'),
        );
    }
}
