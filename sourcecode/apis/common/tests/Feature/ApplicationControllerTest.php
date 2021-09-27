<?php

namespace Tests\Feature;

use App\Models\Application;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function authenticate(string $guard = null)
    {
        $user = new User([
            'id' => '1',
            'isAdmin' => '1'
        ]);

        $this->actingAs($user, $guard);
    }

    public function testListApplications(): void
    {
        $this->authenticate();

        /** @var Collection<Application> $applications */
        $applications = Application::factory()->count(3)->create();

        $this->getJson('/common/applications')
            ->assertOk()
            ->assertJsonCount(3)
            ->assertSimilarJson($applications->toArray());
    }

    public function testCreateApplication(): void
    {
        $this->authenticate();

        $this->postJson('/common/applications', [
            'name' => 'My cool application',
        ])
            ->assertCreated()
            ->assertJson([
                'name' => 'My cool application',
            ]);
    }
}
