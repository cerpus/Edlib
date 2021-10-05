<?php

namespace Tests\Feature\Gdpr\Handlers;

use App\H5PResult;
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

        factory(H5PResult::class,1)->create();
        factory(H5PResult::class,1)->create(['user_id' => $authId]);

        $this->assertCount(2, H5PResult::all());
        $this->assertDatabaseHas('h5p_results', ['user_id' => $authId]);

        $payload = (object)[
            'deletionRequestId' => $this->faker->uuid,
            'userId' => $authId
        ];

        $deletionRequest = new GdprDeletionRequest();
        $deletionRequest->id = $payload->deletionRequestId;
        $deletionRequest->payload = $payload;
        $deletionRequest->save();

        $handler = new H5PResultProcessor();

        $handler->handle($deletionRequest);

        $this->assertCount(1, H5PResult::all());
        $this->assertDatabaseMissing('h5p_results', ['user_id' => $authId]);
    }
}
