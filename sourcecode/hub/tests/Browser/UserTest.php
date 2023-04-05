<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

final class UserTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testUserCanChangeLanguage(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('supersecret'),
            'locale' => 'en',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertDontSee('Mitt innhold')
                ->type('email', 'john@example.com')
                ->type('password', 'supersecret')
                ->press('Log in')
                ->assertAuthenticated()
                ->visit('/preferences')
                ->select('locale', 'nb')
                ->press('Save')
                ->assertSee('Mitt innhold');
        });
    }
}
