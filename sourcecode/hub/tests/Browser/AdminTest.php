<?php

declare(strict_types=1);

namespace Tests\Browser;

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
}
