<?php

namespace Tests\Integration\Models;

use App\Article;
use App\CollaboratorContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollaboratorContextTest extends TestCase
{
    use RefreshDatabase;

    public function testContextShouldUpdatePopulatedTable()
    {
        $ts = Carbon::now()->timestamp;
        CollaboratorContext::factory()->create([
            'timestamp' => Carbon::createFromTimestamp($ts - 10),
            'system_id' => 'mysystem',
            'context_id' => 'mycontext',
        ]);

        $this->assertTrue(CollaboratorContext::contextShouldUpdate('mysystem', 'mycontext', $ts)); // Timestamp is fresher than the one in DB
        $this->assertFalse(CollaboratorContext::contextShouldUpdate('mysystem', 'mycontext', $ts - 100)); // Older timestamp.
    }

    public function testContextShouldUpdateEmptyTable()
    {
        $ts = Carbon::now()->timestamp;
        $this->assertTrue(CollaboratorContext::contextShouldUpdate('mysystem', 'mycontext', $ts));
    }

    public function testCanDeleteContext()
    {
        CollaboratorContext::factory()->create(['context_id' => 'mycontext']);
        CollaboratorContext::factory()->count(10)->create(['system_id' => 'mysystem', 'context_id' => 'mycontext']);
        $this->assertCount(11, CollaboratorContext::all());
        CollaboratorContext::deleteContext('mysystem', 'mycontext');
        $this->assertCount(1, CollaboratorContext::all());
    }

    public function testUpdateContext()
    {
        CollaboratorContext::factory()->create(['system_id' => 'mysystem', 'context_id' => 'mycontext', 'timestamp' => Carbon::now()->timestamp - 2]);
        $collaborators = json_decode(json_encode([
            [
                'type' => 'user',
                'authId' => '1234',
            ],
        ]));

        $resources = json_decode(json_encode([
            [
                "courseId" => 3,
                "moduleId" => 8,
                "activityId" => 39,
                "contentAuthorId" => "2d09c8a1-bdb4-4de0-ac88-dcc9ba18b48e",
                "coreId" => "2a095535-215f-4ee3-a331-b21539675d99",
            ],
            [
                "courseId" => 3,
                "moduleId" => 8,
                "activityId" => 40,
                "contentAuthorId" => "4dd0989a-4c2f-4c88-9b06-6c0bd93a93d0",
                "coreId" => "1a7b023a-6e05-4cc3-9aec-9d92c606c395",
            ],
        ]));

        CollaboratorContext::updateContext('mysystem', 'mycontext', $collaborators, $resources, Carbon::now()->timestamp);

        $this->assertCount(2, CollaboratorContext::all());
    }

    public function testUserHasAccess()
    {
        $userId = '1234';
        $article = Article::factory()->create();
        $this->assertFalse(CollaboratorContext::isUserCollaborator($userId, $article->id));
        $this->assertFalse(CollaboratorContext::isUserCollaborator('just', 'kidding'));

        CollaboratorContext::factory()->create(
            [
                'content_id' => $article->id,
                'collaborator_id' => $userId,
            ],
        );

        $this->assertTrue(CollaboratorContext::isUserCollaborator($userId, $article->id));
        $this->assertFalse(CollaboratorContext::isUserCollaborator('abc', $article->id));
        $this->assertFalse(CollaboratorContext::isUserCollaborator($userId, 'some-id'));
    }
}
