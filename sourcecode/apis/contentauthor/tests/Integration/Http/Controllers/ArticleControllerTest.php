<?php

namespace Tests\Integration\Http\Controllers;

use App\Article;
use App\Events\ArticleWasSaved;
use App\Http\Controllers\ArticleController;
use App\Http\Libraries\License;
use Faker\Provider\Uuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCreate(): void
    {
        $request = Request::create('', parameters: [
            'lti_version' => 'LTI-1p0',
            'lti_message_type' => 'basic-lti-launch-request',
            'resource_link_id' => 'random_link_9364f20a-a9b5-411a-8f60-8a4050f85d91',
            'launch_presentation_return_url' => "https://api.edlib.test/lti/v2/editors/contentauthor/return",
            'ext_user_id' => "1",
            'launch_presentation_locale' => "nb",
        ]);

        $this->withSession([
            'authId' => Uuid::uuid(),
        ]);

        $articleController = app(ArticleController::class);
        $result = $articleController->create($request);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(View::class, $result);
        $data = $result->getData();

        $this->assertArrayHasKey('state', $data);
        $state = json_decode($data['state'], true);
        $this->assertEquals(config('license.default-license'), $state['license']);
    }

    public function testStore(): void
    {
        $this->withSession([
            'authId' => Uuid::uuid(),
        ]);

        $this->expectsEvents([
            ArticleWasSaved::class,
        ]);

        $response = $this->post(route('article.store'), [
            'title' => 'An article',
            'content' => 'Something',
            'origin' => null,
            'originators' => null,
            'isPublished' => false,
            'share' => 'share',
            'license' => License::LICENSE_BY,
        ])
        ->assertCreated();

        $this->assertDatabaseHas('articles', [
            'title' => 'An article',
            'license' => License::LICENSE_BY,
        ]);

        /** @var Article $article */
        $article = Article::where('license', License::LICENSE_BY)->first();
        $response->assertJson([
            'url' => route('article.edit', $article->id),
        ]);
    }
}
