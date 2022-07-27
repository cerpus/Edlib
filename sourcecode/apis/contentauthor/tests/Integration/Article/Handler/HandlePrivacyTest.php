<?php
namespace Tests\Integration\Article\Handler;

use App\Article;
use App\Events\ArticleWasSaved;
use App\Listeners\Article\HandlePrivacy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class HandlePrivacyTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function testHandlePrivacyOnSave()
    {
        $authId = Str::uuid();
        $article = Article::factory()->create(['owner_id' => $authId]);
        $this->assertFalse($article->is_private);

        $request = new Request();
        $request->request->add(['share' => 'PRIVATE']);
        $articleSavedEvent = new ArticleWasSaved($article, $request, collect(), $authId,  'thereason', []);
        $privacyHandler = new HandlePrivacy();
        $privacyHandler->handle($articleSavedEvent);
        $article = $article->fresh();
        $this->assertTrue($article->is_private);

        $request = new Request();
        $request->request->add(['share' => 'share']);
        $articleSavedEvent = new ArticleWasSaved($article, $request, collect(), $authId,  'thereason', []);
        $privacyHandler = new HandlePrivacy();
        $privacyHandler->handle($articleSavedEvent);
        $article = $article->fresh();
        $this->assertFalse($article->is_private);
    }
}
