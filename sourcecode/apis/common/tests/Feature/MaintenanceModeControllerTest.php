<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStatus(): void
    {
        $this->getJson('/common/maintenance_mode')
            ->assertJson([
                'enabled' => false,
            ]);
    }

    public function testToggle(): void
    {
        $this->putJson('/common/maintenance_mode', [
            'enabled' => true,
        ])
            ->assertJson([
                'enabled' => true,
            ]);

        $this->getJson('/common/maintenance_mode')
            ->assertJson([
                'enabled' => true,
            ]);
    }
}
