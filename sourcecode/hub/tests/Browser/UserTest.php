<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\Content;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

final class UserTest extends DuskTestCase
{
    public function testUsersCanSignUpAndBeNotVerified(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertGuest()
                ->type('name', 'Freddie Mercury')
                ->type('email', 'freddie@royal.gov.uk')
                ->type('password', 'scaramouche')
                ->type('password_confirmation', 'scaramouche')
                ->press('Sign up')
                ->assertAuthenticated()
                ->visit('/')
                ->press('Freddie Mercury')
                ->clickLink('My Account')
                ->assertSee('Your email address is unverified.');
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

    public function testEmailIsNormalizedUponRegistration(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertGuest()
                ->type('name', 'E. Mel')
                ->type('email', 'E.MEL@EDLIB.TEST')
                ->type('password', 'my password')
                ->type('password_confirmation', 'my password')
                ->press('Sign up')
                ->assertAuthenticated()
                ->visit('/my-account')
                ->assertInputValue('email', 'e.mel@edlib.test');
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
                ->assertSee('Mitt innhold')
                ->select('locale', 'en')
                ->press('Lagre')
                ->assertSee('My content')
            ;
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
                ->assertSeeIn('aside summary', 'LTI params');
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

    public function testUserCanChangeEmail(): void
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

    public function testEmailIsNormalizedUponChanging(): void
    {
        User::factory()->withEmail('e.mel@edlib.test')->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs('e.mel@edlib.test')
                ->assertAuthenticated()
                ->visit('/my-account')
                ->type('email', 'E.MEL@EDLIB.TEST')
                ->press('Save')
                // The login should be invalid if the email didn't normalize.
                // In that case, we wouldn't be able to see these.
                ->assertSee('Account updated successfully')
                ->assertInputValue('email', 'e.mel@edlib.test'),
        );
    }

    public function testUserCanDisconnectFacebookAndGoogleIDWithPassword(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('supersecret'),
            'facebook_id' => '11198989783333222222',
            'google_id' => '2424224242444643232356',
            'locale' => 'en',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'john@example.com')
                ->type('password', 'supersecret')
                ->press('Log in')
                ->assertAuthenticated()
                ->visit('/my-account')
                ->assertPresent('button[name="disconnect-facebook"]')
                ->assertPresent('button[name="disconnect-google"]')
                ->press('disconnect-facebook')
                ->assertSee('Account updated successfully')
                ->visit('/my-account')
                ->press('disconnect-google')
                ->assertSee('Account updated successfully');
        });
    }

    public function testNotVisibilityOfFacebookAndGoogleDisconnectButtons(): void
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
                ->assertNotPresent('button[name="disconnect-facebook"]')
                ->assertNotPresent('button[name="disconnect-google"]');
        });
    }

    public function testUserCanChangeTheme(): void
    {
        User::factory()->create([
            'email' => 'art.vandelay@example.com',
            'password' => Hash::make('secret123'),
            'theme' => null,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'art.vandelay@example.com')
                ->type('password', 'secret123')
                ->press('Log in')
                ->assertAuthenticated()
                ->assertDataAttribute('', 'bs-theme', 'edlib')
                ->visit('/preferences')
                ->select('theme', 'dark')
                ->press('Save')
                ->waitForLocation('/preferences')
                ->assertDataAttribute('', 'bs-theme', 'dark');
        });
    }

    public function testResetsPassword(): void
    {
        $user = User::factory()
            ->withEmail('goldfish@fishbowl.example')
            ->create();

        $this->assertNull($user->refresh()->password_reset_token);

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('/')
                ->clickLink('Log in')
                ->clickLink('I forgot my password')
                ->type('email', 'goldfish@fishbowl.example')
                ->press('Submit')
                ->assertSee('You should soon receive a password reset link.'),
        );

        $this->assertNotNull($user->refresh()->password_reset_token);
        // FIXME: we cannot test that a mail was sent via Dusk
    }

    public function testPasswordResetDoesNotDistinguishBetweenExistingAndNonexistentEmails(): void
    {
        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('/')
                ->clickLink('Log in')
                ->clickLink('I forgot my password')
                ->type('email', 'nope@nah.example')
                ->press('Submit')
                ->assertSee('You should soon receive a password reset link.'),
        );
        // FIXME: we cannot test that a mail wasn't sent via Dusk
    }
}
