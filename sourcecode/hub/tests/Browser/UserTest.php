<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

final class UserTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testUserCanSignUp(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertGuest()
                ->type('name', 'Freddie Mercury')
                ->type('email', 'freddie@royal.gov.uk')
                ->type('password', 'scaramouche')
                ->type('password_confirmation', 'scaramouche')
                ->press('Sign up')
                ->assertAuthenticated();
        });
    }

    public function testUserCannotSignUpWithDuplicateEmail(): void
    {
        User::factory()->withEmail('duplicate@example.com')->create();

        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertGuest()
                ->type('name', 'Guy Incognito')
                ->type('email', 'duplicate@example.com')
                ->type('password', 'duplicate')
                ->type('password_confirmation', 'duplicate')
                ->press('Sign up')
                ->assertSee('The email has already been taken.')
                ->assertGuest();
        });
    }

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

    public function testUserCanEnableDebugMode(): void
    {
        $content = Content::factory()->withPublishedVersion()->create();
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($content, $user) {
            $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit("/content/{$content->id}")
                ->assertMissing('aside summary')
                ->visit('/preferences')
                ->check('debug_mode')
                ->press('Save')
                ->visit("/content/{$content->id}")
                ->assertSeeIn('aside summary', 'Debug');
        });
    }

    public function testUserCanChangeProfileName(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('supersecret'),
            'locale' => 'en',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'john@example.com')
                ->type('password', 'supersecret')
                ->press('Log in')
                ->assertAuthenticated()
                ->visit('/my-account')
                ->type('name', 'User1_New')
                ->press('Save')
                ->assertSee('User1_New');
        });
    }

    public function testUserCanChangePassword(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('supersecret'),
            'locale' => 'en',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'john@example.com')
                ->type('password', 'supersecret')
                ->press('Log in')
                ->assertAuthenticated()
                ->visit('/my-account')
                ->type('password', '00000000')
                ->type('password_confirmation', '00000000')
                ->press('Save')
                ->assertSee('Account updated successfully')
                ->click('.navbar-nav .nav-link.dropdown-toggle')
                ->press('Log out')
                ->assertGuest()
                ->visit('/login')
                ->type('email', 'john@example.com')
                ->type('password', '00000000')
                ->press('Log in')
                ->assertAuthenticated();
        });
    }

    public function testUserCanChangeEmail():void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('supersecret'),
            'locale' => 'en',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'john@example.com')
                ->type('password', 'supersecret')
                ->press('Log in')
                ->assertAuthenticated()
                ->visit('/my-account')
                ->type('email', 'john_new@example.com')
                ->press('Save')
                ->assertSee('Account updated successfully')
                ->visit('/login')
                ->type('email', 'john_new@example.com')
                ->type('password', 'supersecret')
                ->press('Log in')
                ->assertAuthenticated();
        });
    }
}
