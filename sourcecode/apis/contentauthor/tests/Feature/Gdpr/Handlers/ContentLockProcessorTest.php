<?php

namespace Tests\Feature\Gdpr\Handlers;

use Tests\TestCase;
use App\ContentLock;
use Tests\Traits\WithFaker;
use App\Gdpr\Handlers\ContentLockProcessor;
use Cerpus\Gdpr\Models\GdprDeletionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentLockProcessorTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function testRemovesContentLocksBasedOnAuthId()
    {
        $authId = $this->faker->uuid;
        factory(ContentLock::class, 2)->create();
        factory(ContentLock::class)->create(['auth_id' => $authId]);

        $this->assertCount(3, ContentLock::all());
        $this->assertDatabaseHas('content_locks', ['auth_id' => $authId]);

        $handler = new ContentLockProcessor();
        $payload = (object)[
            'deletionRequestId' => $this->faker->uuid,
            'userId' => $authId
        ];

        $deletionRequest = new GdprDeletionRequest();
        $deletionRequest->id = $payload->deletionRequestId;
        $deletionRequest->payload = $payload;
        $deletionRequest->save();

        $handler->handle($deletionRequest);

        $this->assertCount(2, ContentLock::all());
        $this->assertDatabaseMissing('content_locks', ['auth_id' => $authId]);
    }

    public function testRemovesContentLocksBasedOnEmail()
    {
        $email = 'test@example.com';

        factory(ContentLock::class, 2)->create();
        factory(ContentLock::class)->create(['email' => $email]);

        $this->assertCount(3, ContentLock::all());
        $this->assertDatabaseHas('content_locks', ['email' => $email]);

        $handler = new ContentLockProcessor();

        $payLoad = (object)[
            'deletionRequestId' => $this->faker->uuid,
            'userId' => $this->faker->uuid,
            'emails' => ['test@example.com']
        ];

        $deletionRequest = new GdprDeletionRequest();
        $deletionRequest->id = $payLoad->deletionRequestId;
        $deletionRequest->payload = $payLoad;
        $deletionRequest->save();

        $handler->handle($deletionRequest);

        $this->assertCount(2, ContentLock::all());
        $this->assertDatabaseMissing('content_locks', ['email' => $email]);
    }
}
