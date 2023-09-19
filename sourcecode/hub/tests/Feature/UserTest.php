<?php

declare(strict_types=1);

namespace Feature;

use App\Models\User;
use Tests\TestCase;
use function json_decode;
use const JSON_THROW_ON_ERROR;

class UserTest extends TestCase
{
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
}
