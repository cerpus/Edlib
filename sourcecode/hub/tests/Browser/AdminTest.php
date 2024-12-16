<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\LtiPlatform;
use App\Models\LtiTool;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\LtiPlatformAddedAlert;
use Tests\Browser\Components\LtiPlatformCard;
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
            fn (Browser $browser) => $browser
                ->loginAs(User::factory()->admin()->create()->email)
                ->visit('/admin/lti-platforms')
                ->resize(1920, 1920)
                ->with(
                    'main .lti-platform-card',
                    fn (Browser $main) => $main
                        ->press('Remove')
                )
                ->waitFor('#htmxConfirmModal')
                ->with(
                    '#htmxConfirmModal',
                    fn (Browser $modal) => $modal
                        ->assertSee('Are you sure you want to remove the LTI platform?')
                        ->press('OK')
                )
                ->waitForReload()
                ->assertNotPresent('.lti-platform-card')
                ->assertSee('The LTI platform has been removed')
        );
    }

    public function testCanAddLtiPlatforms(): void
    {
        $user = User::factory()->admin()->name('Admin User')->create();

        $this->browse(
            fn (Browser $browser) => $browser
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
                    fn (Browser $card) => $card
                        ->assertSeeIn('@enable-sso', 'Yes')
                        ->assertSeeIn('@authorizes-edit', 'Yes')
                )
        );
    }

    public function testCanEditLtiPlatform(): void
    {
        LtiPlatform::factory()->name('Old name')->create();
        $user = User::factory()->admin()->name('Admin User')->create();

        $this->browse(
            fn (Browser $browser) => $browser
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
                    fn (Browser $card) => $card
                        ->assertSeeIn('@title', 'New name')
                        ->assertSeeIn('@enable-sso', 'No')
                )
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
            fn (Browser $browser) => $browser
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
                    fn (Browser $frame) => $frame
                        ->press('Resize to 640')
                )
        );
    }

    public function testCreatesToolsWithSlug(): void
    {
        $platform = LtiPlatform::factory()->create();
        $user = User::factory()->admin()->create();

        $this->browse(
            fn (Browser $browser) =>
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
                ->clickLink('The tool')
                ->assertUrlIs('https://hub-test.edlib.test/content/create/the-tool')
                ->assertPresent('.lti-launch')
        );
    }
}
