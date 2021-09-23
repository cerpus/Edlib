<?php

namespace Tests\Feature;

use App\Models\Application;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ApplicationControllerTest extends TestCase
{
    public function testListApplications(): void
    {
        /** @var Collection<Application> $applications */
        $applications = Application::factory()->count(3)->create();

        $this->getJson('/api/applications')
            ->assertOk()
            ->assertJsonCount(3)
            ->assertSimilarJson($applications->toArray());
    }

    public function testCreateApplication(): void
    {
        $this->postJson('/api/applications', [
            'name' => 'My cool application',
        ])
            ->assertCreated()
            ->assertJson([
                'name' => 'My cool application',
            ]);
    }
}
