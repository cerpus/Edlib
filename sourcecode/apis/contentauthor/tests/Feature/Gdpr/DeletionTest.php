<?php

namespace Tests\Feature\Gdpr;

use Tests\TestCase;
use App\Gdpr\Deletion;
use Tests\Traits\WithFaker;
use Cerpus\Gdpr\Models\GdprDeletionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeletionTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function testExample()
    {
        $authId = $this->faker->uuid;

        $payload = (object)[
            'deletionRequestId' => $this->faker->uuid,
            'userId' => $authId
        ];

        $deletionRequest = new GdprDeletionRequest();
        $deletionRequest->id = $payload->deletionRequestId;
        $deletionRequest->payload = $payload;
        $deletionRequest->save();

        $this->assertDatabaseMissing('gdpr_logs', ['message' => 'Handled Shares.']);
        $this->assertDatabaseMissing('gdpr_logs', ['message' => 'Handled Context Shares.']);
        $this->assertDatabaseMissing('gdpr_logs', ['message' => 'Handled H5P Results.']);
        $this->assertDatabaseMissing('gdpr_logs', ['message' => 'Handled Content Locks.']);

        $handler = new Deletion();

        $logCountStart = $deletionRequest->fresh()->logs->count();

        $handler->delete($deletionRequest);

        $deletionRequest = $deletionRequest->fresh();

        $logCountEnd = $deletionRequest->logs->count();

        $this->assertTrue($logCountEnd > $logCountStart);

        $this->assertDatabaseHas('gdpr_logs', ['message' => 'Handled Shares.']);
        $this->assertDatabaseHas('gdpr_logs', ['message' => 'Handled Context Shares.']);
        $this->assertDatabaseHas('gdpr_logs', ['message' => 'Handled H5P Results.']);
        $this->assertDatabaseHas('gdpr_logs', ['message' => 'Handled Content Locks.']);
    }
}
