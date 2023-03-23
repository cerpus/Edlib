<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

final class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminsCanViewTheAdminHome(): void
    {
        $user = User::factory()->admin()->hasLogin()->create()->login
            ?? throw new LogicException('Expected login');

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }

    public function testCannotShowAdminHomeWhenLoggedOut(): void
    {
        $this->get('/admin')
            ->assertForbidden();
    }

    public function testCannotShowAdminHomeWhenNotAdmin(): void
    {
        $login = User::factory()->hasLogin()->create()->login
            ?? throw new LogicException('Expected login');

        $this->actingAs($login)
            ->get('/admin')
            ->assertForbidden();
    }
}
