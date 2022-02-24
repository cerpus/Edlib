<?php

namespace Tests\Feature\Gdpr\Handlers;

use App\Messaging\Messages\EdlibGdprDeleteMessage;
use Tests\TestCase;
use App\ContentLock;
use Tests\Traits\MockRabbitMQPubsub;
use Tests\Traits\WithFaker;
use App\Gdpr\Handlers\ContentLockProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentLockProcessorTest extends TestCase
{
    use WithFaker, RefreshDatabase, MockRabbitMQPubsub;

    public function setUp(): void
    {
        parent::setUp();
        $this->setupRabbitMQPubSub();
    }

    public function testRemovesContentLocksBasedOnAuthId()
    {
        $authId = $this->faker->uuid;
        ContentLock::factory()->count(2)->create();
        ContentLock::factory()->create(['auth_id' => $authId]);

        $this->assertCount(3, ContentLock::all());
        $this->assertDatabaseHas('content_locks', ['auth_id' => $authId]);

        $handler = new ContentLockProcessor();

        $deletionRequest = new EdlibGdprDeleteMessage([
            'requestId' => $this->faker->uuid,
            'userId' => $authId,
            'emails' => []
        ]);

        $handler->handle($deletionRequest);

        $this->assertCount(2, ContentLock::all());
        $this->assertDatabaseMissing('content_locks', ['auth_id' => $authId]);
    }

    public function testRemovesContentLocksBasedOnEmail()
    {
        $email = 'test@example.com';

        ContentLock::factory()->count(2)->create();
        ContentLock::factory()->create(['email' => $email]);

        $this->assertCount(3, ContentLock::all());
        $this->assertDatabaseHas('content_locks', ['email' => $email]);

        $handler = new ContentLockProcessor();

        $deletionRequest = new EdlibGdprDeleteMessage([
            'requestId' => $this->faker->uuid,
            'userId' => $this->faker->uuid,
            'emails' => ['test@example.com']
        ]);

        $handler->handle($deletionRequest);

        $this->assertCount(2, ContentLock::all());
        $this->assertDatabaseMissing('content_locks', ['email' => $email]);
    }
}
