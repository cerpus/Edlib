<?php
namespace Tests\Article\Handler;

use App\Article;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\ArticleWasSaved;
use App\Http\Libraries\License;
use App\Listeners\Article\HandleLicensing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class HandleLicensingTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testHandleLicensingOnSave()
    {
        $this->markTestSkipped("This test does not test anything!");
        $this->setUpLicensing();
        $authId = Str::uuid();
        $article = Article::factory()->create(['owner_id' => $authId]);

        $request = new Request();
        $request->request->set('license', 'BY');

        $articleSavedEvent = new ArticleWasSaved($article, $request, collect(), $authId,  'thereason', []);

        $licensingHandler = new HandleLicensing();
        $licensingHandler->handle($articleSavedEvent);

        $article->fresh();

    }

    protected function setUpLicensing()
    {
        $license = $this->getMockBuilder(License::class)
            ->getMock();

        $licJson = json_decode('[
                {
                    "id": "CC0",
                    "name": "Creative Commons"
                },
                {
                    "id": "BY",
                    "name": "CC Attribution"
                }
            ]');

        $license->method("getLicenses")->willReturn($licJson);
        $license->method("getLicense")->willReturn('BY');
        $license->method("getOrAddContent")->willReturn(json_decode('{}'));
        $license->method("setLicense")->willReturn(json_encode([ 'id' => Str::uuid()]));

        app()->instance(License::class, $license);
    }

}
