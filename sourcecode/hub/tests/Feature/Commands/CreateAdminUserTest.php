<?php

namespace Tests\Feature\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class CreateAdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function testUserIsNotAuthenticatableOrAdmin(): void
    {
        $this->assertFalse(Auth::validate([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]), "User logged in, but shouldn't have");

        $this->artisan('edlib:create-admin-user', ['email' => 'john@example.com'])
            ->expectsQuestion('Enter a password for the user', 'password123')
            ->assertExitCode(0);

        $this->assertTrue(Auth::validate([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]));

        $login = User::where('email', 'john@example.com')->firstOrFail();

        $this->assertTrue(Gate::forUser($login)->allows('admin'));
    }
}
