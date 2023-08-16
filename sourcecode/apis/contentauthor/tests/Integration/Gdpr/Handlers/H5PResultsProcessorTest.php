<?php

namespace Tests\Integration\Gdpr\Handlers;

use App\Gdpr\Handlers\H5PResultProcessor;
use App\H5PResult;
use App\Messaging\Messages\EdlibGdprDeleteMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Helpers\MockRabbitMQPubsub;
use Tests\TestCase;

class H5PResultsProcessorTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;
    use MockRabbitMQPubsub;

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
