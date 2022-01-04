<?php
namespace Tests\Article\Handler;

use App\Article;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\ArticleWasSaved;
use App\Listeners\Article\HandleCollaborators;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class HandleCollaboratorsTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

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
