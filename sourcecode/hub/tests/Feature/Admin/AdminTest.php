<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\LtiTool;
use App\Models\LtiToolExtra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminsCanViewTheAdminHome(): void
    {
        $user = User::factory()->admin()->create();

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
        $login = User::factory()->create();

        $this->actingAs($login)
            ->get('/admin')
            ->assertForbidden();
    }

    public function testNonAdminsCannotUseAdminEndpoints(): void
    {
        $tool = LtiTool::factory()->extra(LtiToolExtra::factory()->admin())->create();

        $this->actingAs(User::factory()->create())
            ->get('/content/create/' . $tool->id . '/' . $tool->extras->firstOrFail()->id)
            ->assertForbidden();
    }
}
