<?php

namespace Tests\Integration\Article\Handler;

use App\Article;
use App\Events\ArticleWasSaved;
use App\Listeners\Article\HandleCollaborators;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class HandleCollaboratorsTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function testHandleCollaboratorsOnSave()
    {
        $authId = Str::uuid();
        $article = Article::factory()->create(['owner_id' => $authId]);

        $request = new Request();

        $articleSavedEvent = new ArticleWasSaved($article, $request, collect(['a@b.com']), $authId, 'thereason', []);

        $collaboratorHandler = new HandleCollaborators();
        $collaboratorHandler->handle($articleSavedEvent);

        $article->fresh();

        $theCollaborator = $article->collaborators()->where('email', 'a@b.com')->first();
        $this->assertNotNull($theCollaborator);

        $this->assertDatabaseHas('article_collaborators', ['article_id' => $article->id, 'email' => 'a@b.com']);
    }
}
