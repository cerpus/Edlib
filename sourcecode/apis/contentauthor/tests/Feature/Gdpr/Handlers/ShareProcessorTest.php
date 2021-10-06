<?php

namespace Tests\Feature\Gdpr\Handlers;

use App\Game;
use App\Article;
use App\H5PContent;
use Tests\TestCase;
use App\QuestionSet;
use App\Collaborator;
use App\H5PCollaborator;
use Tests\Traits\WithFaker;
use App\ArticleCollaborator;
use App\Gdpr\Handlers\ShareProcessor;
use Cerpus\Gdpr\Models\GdprDeletionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShareProcessorTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function testRemovesSharesFromArticles()
    {
        $email = 'test@example.com';
        $article = factory(Article::class)->create();
        factory(ArticleCollaborator::class)->create(['article_id' => $article->id]);
        factory(ArticleCollaborator::class)->create(['article_id' => $article->id, 'email' => $email]);

        $this->assertCount(2, $article->fresh()->collaborators);
        $this->assertDatabaseHas('article_collaborators', ['email' => $email]);

        $payload = (object)[
            'deletionRequestId' => $this->faker->uuid,
            'userId' => $this->faker->uuid,
            'emails' => [$email]
        ];

        $deletionRequest = new GdprDeletionRequest();
        $deletionRequest->id = $payload->deletionRequestId;
        $deletionRequest->payload = $payload;
        $deletionRequest->save();

        $handler = new ShareProcessor();

        $handler->handle($deletionRequest);

        $this->assertCount(1, $article->fresh()->collaborators);
        $this->assertDatabaseMissing('article_collaborators', ['email' => $email]);
    }

    public function testRemovesSharesFromH5Ps()
    {
        $email = 'test@example.com';
        $h5p = factory(H5PContent::class)->create();

        factory(H5PCollaborator::class)->create(['h5p_id' => $h5p->id]);
        factory(H5PCollaborator::class)->create(['h5p_id' => $h5p->id, 'email' => $email]);

        $this->assertCount(2, $h5p->fresh()->collaborators);
        $this->assertDatabaseHas('cerpus_contents_shares', ['email' => $email]);

        $payload = (object)[
            'deletionRequestId' => $this->faker->uuid,
            'userId' => $this->faker->uuid,
            'emails' => [$email]
        ];

        $deletionRequest = new GdprDeletionRequest();
        $deletionRequest->id = $payload->deletionRequestId;
        $deletionRequest->payload = $payload;
        $deletionRequest->save();

        $handler = new ShareProcessor();

        $handler->handle($deletionRequest);

        $this->assertCount(1, $h5p->fresh()->collaborators);
        $this->assertDatabaseMissing('cerpus_contents_shares', ['email' => $email]);
    }

    public function testRemovesSharesFromQuestionSetsAndGames()
    {
        $email = 'test@example.com';

        $questionSet = factory(QuestionSet::class)->create();
        $questionSet->setCollaborators([$email, $this->faker->email]);

        $game = factory(Game::class)->create();
        $game->setCollaborators([$email, $this->faker->email]);
        $anotherGame = factory(Game::class)->create();
        $anotherGame->setCollaborators([$this->faker->email]);

        $this->assertCount(5, Collaborator::all());
        $this->assertCount(2, $questionSet->fresh()->collaborators);
        $this->assertCount(2, $game->fresh()->collaborators);
        $this->assertCount(1, $anotherGame->fresh()->collaborators);
        $this->assertDatabaseHas('collaborators', ['email' => $email]);

        $payload = (object)[
            'deletionRequestId' => $this->faker->uuid,
            'userId' => $this->faker->uuid,
            'emails' => [$email]
        ];

        $deletionRequest = new GdprDeletionRequest();
        $deletionRequest->id = $payload->deletionRequestId;
        $deletionRequest->payload = $payload;
        $deletionRequest->save();

        $handler = new ShareProcessor();

        $handler->handle($deletionRequest);

        $this->assertCount(3, Collaborator::all());
        $this->assertCount(1, $questionSet->fresh()->collaborators);
        $this->assertCount(1, $game->fresh()->collaborators);
        $this->assertCount(1, $anotherGame->fresh()->collaborators);
        $this->assertDatabaseMissing('collaborators', ['email' => $email]);
    }
}
