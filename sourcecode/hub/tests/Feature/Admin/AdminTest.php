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
        LtiTool::factory()
            ->slug('the-tool')
            ->extra(LtiToolExtra::factory()->slug('the-extra')->admin())
            ->create();

        $this->actingAs(User::factory()->create())
            ->get('/content/create/the-tool/the-extra')
            ->assertForbidden();
    }

    public function testCannotAccessAdminsAsNonAdmin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/admins')
            ->assertForbidden();
    }

    public function testAdminCanSearchContentExclusions(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get('/admin/content-exclusions/search')
            ->assertOk();
    }

    public function testAdminCanSearchMultipleContentExclusionsByIds(): void
    {
        $user = User::factory()->admin()->create();
        $content1 = \App\Models\Content::factory()->hasVersions(1)->create();
        $content2 = \App\Models\Content::factory()->hasVersions(1)->create();

        // Without spaces
        $this->actingAs($user)
            ->get('/admin/content-exclusions/search?contentId=' . $content1->id . ',' . $content2->id)
            ->assertOk()
            ->assertSee($content1->id)
            ->assertSee($content2->id);

        // With spaces and non-existent ID
        $this->actingAs($user)
            ->get('/admin/content-exclusions/search?contentId=' . $content1->id . ', ' . $content2->id . ', non_existent')
            ->assertOk()
            ->assertSee($content1->id)
            ->assertSee($content2->id);
    }

    public function testAdminCanSearchMultipleContentExclusionsByTitles(): void
    {
        $user = User::factory()->admin()->create();
        $content1 = \App\Models\Content::factory()->hasVersions(1, ['title' => 'First Content Title'])->create();
        $content2 = \App\Models\Content::factory()->hasVersions(1, ['title' => 'Second Content Title'])->create();

        // Without spaces
        $this->actingAs($user)
            ->get('/admin/content-exclusions/search?title=' . urlencode('First Content Title,Second Content Title'))
            ->assertOk()
            ->assertSee('First Content Title')
            ->assertSee('Second Content Title');

        // With spaces and short title filtered out
        $this->actingAs($user)
            ->get('/admin/content-exclusions/search?title=' . urlencode('First Content Title, Second Content Title, AB'))
            ->assertOk()
            ->assertSee('First Content Title')
            ->assertSee('Second Content Title');
    }
}
