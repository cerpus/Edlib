<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class JWTUpdateControllerTest extends TestCase
{
    use WithFaker;

    public function testUpdateJwt(): void
    {
        $payload = [
            'sub' => (string) $this->faker->unixTime,
            'iat' => $this->faker->unixTime,
        ];

        $token = JWT::encode(
            $payload,
            file_get_contents(__DIR__ . '/../../../jwt-test.key'),
            'RS256',
        );

        $this->postJson('/jwt/update', ['jwt' => $token])
            ->assertOk()
            ->assertSessionHas('jwtToken', ['raw' => $token]);
    }
}
