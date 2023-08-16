<?php

namespace Tests\Integration\Gdpr\Handlers;

use App\CollaboratorContext;
use App\Gdpr\Handlers\ContextShareProcessor;
use App\Messaging\Messages\EdlibGdprDeleteMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Helpers\MockRabbitMQPubsub;
use Tests\TestCase;

class ContextShareProcessorTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;
    use MockRabbitMQPubsub;

    public function testContextSharesAreRemoved()
    {
        $authId = $this->faker->uuid;
        CollaboratorContext::factory()->create(['collaborator_id' => $authId, 'content_id' => 1]);
        CollaboratorContext::factory()->count(2)->create();

        $this->assertCount(3, CollaboratorContext::all());
        $this->assertDatabaseHas('collaborator_contexts', ['collaborator_id' => $authId, 'content_id' => 1]);
        $this->assertTrue(CollaboratorContext::isUserCollaborator($authId, 1));

        $handler = new ContextShareProcessor();

        $deletionRequest = new EdlibGdprDeleteMessage([
            'requestId' => $this->faker->uuid,
            'userId' => $authId,
            'emails' => []
        ]);

        $handler->handle($deletionRequest);

        $this->assertCount(2, CollaboratorContext::all());
        $this->assertDatabaseMissing('collaborator_contexts', ['collaborator_id' => $authId]);
        $this->assertFalse(CollaboratorContext::isUserCollaborator($authId, 1));
    }
}
