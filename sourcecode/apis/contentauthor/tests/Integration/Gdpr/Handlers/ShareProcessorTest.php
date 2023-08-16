<?php

namespace Tests\Integration\Gdpr\Handlers;

use App\Article;
use App\ArticleCollaborator;
use App\Collaborator;
use App\Game;
use App\Gdpr\Handlers\ShareProcessor;
use App\H5PCollaborator;
use App\H5PContent;
use App\Messaging\Messages\EdlibGdprDeleteMessage;
use App\QuestionSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Helpers\MockRabbitMQPubsub;
use Tests\TestCase;

class ShareProcessorTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;
    use MockRabbitMQPubsub;

    public function testRemovesSharesFromArticles()
    {
        $email = 'test@example.com';
        $article = Article::factory()->create();
        ArticleCollaborator::factory()->create(['article_id' => $article->id]);
        ArticleCollaborator::factory()->create(['article_id' => $article->id, 'email' => $email]);

        $this->assertCount(2, $article->fresh()->collaborators);
        $this->assertDatabaseHas('article_collaborators', ['email' => $email]);

        $deletionRequest = new EdlibGdprDeleteMessage([
            'requestId' => $this->faker->uuid,
            'userId' => $this->faker->uuid,
            'emails' => [$email]
        ]);

        $handler = new ShareProcessor();

        $handler->handle($deletionRequest);

        $this->assertCount(1, $article->fresh()->collaborators);
        $this->assertDatabaseMissing('article_collaborators', ['email' => $email]);
    }

    public function testRemovesSharesFromH5Ps()
    {
        $email = 'test@example.com';
        $h5p = H5PContent::factory()->create();

        H5PCollaborator::factory()->create(['h5p_id' => $h5p->id]);
        H5PCollaborator::factory()->create(['h5p_id' => $h5p->id, 'email' => $email]);

        $this->assertCount(2, $h5p->fresh()->collaborators);
        $this->assertDatabaseHas('cerpus_contents_shares', ['email' => $email]);

        $deletionRequest = new EdlibGdprDeleteMessage([
            'requestId' => $this->faker->uuid,
            'userId' => $this->faker->uuid,
            'emails' => [$email]
        ]);

        $handler = new ShareProcessor();

        $handler->handle($deletionRequest);

        $this->assertCount(1, $h5p->fresh()->collaborators);
        $this->assertDatabaseMissing('cerpus_contents_shares', ['email' => $email]);
    }

    public function testRemovesSharesFromQuestionSetsAndGames()
    {
        $email = 'test@example.com';

        $questionSet = QuestionSet::factory()->create();
        $questionSet->setCollaborators([$email, $this->faker->email]);

        $game = Game::factory()->create();
        $game->setCollaborators([$email, $this->faker->email]);
        $anotherGame = Game::factory()->create();
        $anotherGame->setCollaborators([$this->faker->email]);

        $this->assertCount(5, Collaborator::all());
        $this->assertCount(2, $questionSet->fresh()->collaborators);
        $this->assertCount(2, $game->fresh()->collaborators);
        $this->assertCount(1, $anotherGame->fresh()->collaborators);
        $this->assertDatabaseHas('collaborators', ['email' => $email]);


        $deletionRequest = new EdlibGdprDeleteMessage([
            'requestId' => $this->faker->uuid,
            'userId' => $this->faker->uuid,
            'emails' => [$email]
        ]);

        $handler = new ShareProcessor();

        $handler->handle($deletionRequest);

        $this->assertCount(3, Collaborator::all());
        $this->assertCount(1, $questionSet->fresh()->collaborators);
        $this->assertCount(1, $game->fresh()->collaborators);
        $this->assertCount(1, $anotherGame->fresh()->collaborators);
        $this->assertDatabaseMissing('collaborators', ['email' => $email]);
    }
}
