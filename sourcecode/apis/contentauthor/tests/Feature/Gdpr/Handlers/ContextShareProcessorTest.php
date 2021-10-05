<?php

namespace Tests\Feature\Gdpr\Handlers;

use Tests\TestCase;
use Tests\Traits\WithFaker;
use App\CollaboratorContext;
use Cerpus\Gdpr\Models\GdprDeletionRequest;
use App\Gdpr\Handlers\ContextShareProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContextShareProcessorTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function testContextSharesAreRemoved()
    {
        $authId = $this->faker->uuid;
        factory(CollaboratorContext::class)->create(['collaborator_id' => $authId, 'content_id' => 1]);
        factory(CollaboratorContext::class, 2)->create();

        $this->assertCount(3, CollaboratorContext::all());
        $this->assertDatabaseHas('collaborator_contexts', ['collaborator_id' => $authId, 'content_id' => 1]);
        $this->assertTrue(CollaboratorContext::isUserCollaborator($authId, 1));

        $handler = new ContextShareProcessor();

        $payLoad = (object)[
            'deletionRequestId' => $this->faker->uuid,
            'userId' => $authId,
        ];

        $deletionRequest = new GdprDeletionRequest();
        $deletionRequest->id = $payLoad->deletionRequestId;
        $deletionRequest->payload = $payLoad;
        $deletionRequest->save();

        $handler->handle($deletionRequest);

        $this->assertCount(2, CollaboratorContext::all());
        $this->assertDatabaseMissing('collaborator_contexts', ['collaborator_id' => $authId]);
        $this->assertFalse(CollaboratorContext::isUserCollaborator($authId, 1));
    }
}
