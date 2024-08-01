<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\LtiPlatform;
use App\Models\LtiTool;
use App\Models\User;
use Laravel\Dusk\Browser;
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
                ->with(
                    'main .lti-platform',
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
                ->assertNotPresent('.lti-platform')
                ->assertSee('The LTI platform has been removed')
        );
    }
}
