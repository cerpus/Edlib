<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

final class CreateAdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function testCreatesAdminUsers(): void
    {
        $this->assertFalse(Auth::validate([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]));

        $this->artisan('edlib:create-admin-user', ['email' => 'john@example.com'])
            ->expectsQuestion('Enter a password for the user', 'password123')
            ->assertExitCode(0);

        $this->assertTrue(Auth::validate([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]));
    }
}
