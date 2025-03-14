<?php

namespace Tests\Integration\Http\Controllers;

use App\Article;
use App\Events\ArticleWasSaved;
use App\Http\Libraries\License;
use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use Faker\Provider\Uuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCreate(): void
    {
        $request = new Oauth1Request('POST', url('/article/create'), [
            'lti_version' => 'LTI-1p0',
            'lti_message_type' => 'basic-lti-launch-request',
            'resource_link_id' => 'random_link_9364f20a-a9b5-411a-8f60-8a4050f85d91',
            'launch_presentation_return_url' => "https://api.edlib.test/lti/v2/editors/contentauthor/return",
            'ext_user_id' => "1",
            'launch_presentation_locale' => "nb",
        ]);

        $request = $this->app->make(SignerInterface::class)->sign(
            $request,
            $this->app->make(CredentialStoreInterface::class),
        );

        $result = $this->withSession(['authId' => Uuid::uuid()])
            ->post('/article/create', $request->toArray())
            ->assertOk()
            ->original;

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(View::class, $result);
        $data = $result->getData();

        $this->assertArrayHasKey('state', $data);
        $state = json_decode($data['state'], true);
        $this->assertEquals(config('license.default-license'), $state['license']);
    }

    public function testStore(): void
    {
        Event::fake();
        $this->withSession([
            'authId' => Uuid::uuid(),
        ]);

        $response = $this->post(route('article.store'), [
            'title' => 'An article',
            'content' => 'Something',
            'origin' => null,
            'originators' => null,
            'isShared' => true,
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

        Event::assertDispatched(ArticleWasSaved::class);
    }
}
