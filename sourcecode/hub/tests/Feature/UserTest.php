<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Stub\SocialiteUser;
use Tests\TestCase;

use function json_decode;

use const JSON_THROW_ON_ERROR;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testSignupsAreUsuallyEnabled(): void
    {
        $this->get('/register')->assertOk();
    }

    public function testSignupCanBeDisabled(): void
    {
        config()->set('features.sign-up', false);

        $this->get('/register')->assertForbidden();
    }

    public function testForgotPasswordIsUsuallyEnabled(): void
    {
        $this->get('/forgot-password')->assertOk();
    }

    public function testForgotPasswordCanBeDisabled(): void
    {
        config()->set('features.forgot-password', false);

        $this->get('/forgot-password')->assertForbidden();
    }

    public function testSerialisation(): void
    {
        $user = User::factory()
            ->withGoogleId()
            ->withFacebookId()
            ->create();

        $data = json_decode(
            json_encode($user, flags: JSON_THROW_ON_ERROR),
            associative: true,
        );

        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($user->id, $data['id']);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayNotHasKey('email', $data);
        $this->assertArrayNotHasKey('google_id', $data);
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('password_reset_token', $data);
        $this->assertArrayNotHasKey('google_id', $data);
        $this->assertArrayNotHasKey('facebook_id', $data);
    }

    public function testFindsSocialAccountById(): void
    {
        $details = new SocialiteUser();

        $user = User::factory()->create([
            'name' => 'Not From Details',
            'email' => 'something@different',
            'google_id' => $details->getId(),
        ]);

        $found = User::fromSocial('google', $details);

        $this->assertTrue($found->is($user));
        $this->assertSame($details->getId(), $found->google_id);
        $this->assertSame('Not From Details', $found->name);
        $this->assertSame('something@different', $found->email);
    }

    public function testFindsSocialAccountByEmail(): void
    {
        $details = new SocialiteUser();

        $user = User::factory()->create([
            'name' => 'Not From Details',
            'email' => $details->getEmail(),
            'facebook_id' => 'non-matching',
        ]);

        $found = User::fromSocial('facebook', $details);

        $this->assertTrue($found->is($user));
        $this->assertSame($details->getId(), $found->facebook_id);
        $this->assertSame('Not From Details', $found->name);
        $this->assertSame($details->getEmail(), $found->email);
    }

    public function testCreatesSocialAccount(): void
    {
        $details = new SocialiteUser();

        $created = User::fromSocial('auth0', $details);

        $this->assertTrue($created->wasRecentlyCreated);
        $this->assertSame($details->getId(), $created->auth0_id);
        $this->assertSame($details->getName(), $created->name);
        $this->assertSame($details->getEmail(), $created->email);
    }
}
