<?php

namespace Tests\Feature\Gdpr\Handlers;

use App\H5PResult;
use App\Messaging\Messages\EdlibGdprDeleteMessage;
use Tests\TestCase;
use Tests\Traits\WithFaker;
use App\Gdpr\Handlers\H5PResultProcessor;
use Cerpus\Gdpr\Models\GdprDeletionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class H5PResultsProcessorTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function testResultsAreDeleted()
    {
        $authId = $this->faker->uuid;

        H5PResult::factory()->create();
        H5PResult::factory()->create(['user_id' => $authId]);

        $this->assertCount(2, H5PResult::all());
        $this->assertDatabaseHas('h5p_results', ['user_id' => $authId]);

        $deletionRequest = new EdlibGdprDeleteMessage([
            'requestId' => $this->faker->uuid,
            'userId' => $authId,
            'emails' => []
        ]);

        $handler = new H5PResultProcessor();

        $handler->handle($deletionRequest);

        $this->assertCount(1, H5PResult::all());
        $this->assertDatabaseMissing('h5p_results', ['user_id' => $authId]);
    }
}
