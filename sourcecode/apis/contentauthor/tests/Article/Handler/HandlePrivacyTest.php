<?php
namespace Tests\Article\Handler;

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\ArticleWasSaved;
use Tests\Traits\MockMetadataService;
use App\Listeners\Article\HandlePrivacy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class HandlePrivacyTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware, MockMetadataService;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testHandlePrivacyOnSave()
    {

        $this->setupMetadataService([
            'createData' => true
        ]);

        $authId = Str::uuid();
        $article = factory(\App\Article::class)->create(['owner_id' => $authId]);
        $this->assertNotEquals(1, $article->is_private);

        $request = new Request();
        $request->request->add(['share' => 'PRIVATE']);
        $articleSavedEvent = new ArticleWasSaved($article, $request, collect(), $authId,  'thereason', []);
        $privacyHandler = new HandlePrivacy();
        $privacyHandler->handle($articleSavedEvent);
        $article = $article->fresh();
        $this->assertEquals(1, $article->is_private);

        $request = new Request();
        $request->request->add(['share' => 'share']);
        $articleSavedEvent = new ArticleWasSaved($article, $request, collect(), $authId,  'thereason', []);
        $privacyHandler = new HandlePrivacy();
        $privacyHandler->handle($articleSavedEvent);
        $article = $article->fresh();
        $this->assertEquals(0, $article->is_private);
    }
}
