<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function testAuthorizedWithValidToken(): void
    {
        $user = User::factory()->create();

        $this
            ->withBasicAuth($user->getApiKey(), $user->getApiSecret())
            ->getJson('/api')
            ->assertOk()
            ->assertJson([
                'it worked' => true,
                'user' => [
                    'id' => $user->id,
                ],
            ]);
    }

    public function testUnauthorizedWithMissingAuthorization(): void
    {
        $this->getJson('/api')
            ->assertUnauthorized();
    }

    public function testUnauthorizedWithInvalidSecret(): void
    {
        $user = User::factory()->create();

        $this
            ->withBasicAuth($user->getApiKey(), 'invalid')
            ->getJson('/api')
            ->assertUnauthorized();
    }

    public function testUnauthorizedWithInvalidKey(): void
    {
        $this
            ->withBasicAuth('invalid', 'invalid')
            ->getJson('/api')
            ->assertUnauthorized();
    }
}
