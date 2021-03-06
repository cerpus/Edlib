<?php

namespace Tests\Integration\Article;

use App\ApiModels\User;
use App\Article;
use App\Events\ArticleWasSaved;
use App\Events\ContentCreated;
use App\Events\ContentCreating;
use App\H5pLti;
use App\Http\Middleware\VerifyCsrfToken;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Helpers\MockAuthApi;
use Tests\Helpers\MockResourceApi;
use Tests\Helpers\MockVersioningTrait;

class ArticleTest extends TestCase
{
    use RefreshDatabase, MockVersioningTrait, MockResourceApi, MockAuthApi;

    public function testEditArticleAccessDenied()
    {
        $this->setUpResourceApi();
        $authId = Str::uuid();
        $someOtherId = Str::uuid();

        $article = Article::factory()->create([
            'owner_id' => $authId,
            'license' => 'BY-NC-ND',
        ]);

        $this->withSession(['authId' => $someOtherId])
            ->get(route('article.edit', $article->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testCreateArticle()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->expectsEvents([
            ContentCreating::class,
            ArticleWasSaved::class,
            ContentCreated::class,
        ]);
        $authId = Str::uuid();

        $testAdapter = $this->createStub(H5PAdapterInterface::class);
        $testAdapter->method('isUserPublishEnabled')->willReturn(false);
        $testAdapter->method('getAdapterName')->willReturn("UnitTest");
        app()->instance(H5PAdapterInterface::class, $testAdapter);

        $this->withSession(['authId' => $authId])
            ->post(route('article.store'), [
                'title' => "Title",
                'content' => "Content",
                'license' => 'PRIVATE',
            ]);
        $this->assertDatabaseHas('articles', ['title' => 'Title', 'content' => 'Content', 'is_published' => 1]);
    }

    public function testCreateArticleWithMathContent()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        Event::fake();
        $authId = Str::uuid();

        $testAdapter = $this->createStub(H5PAdapterInterface::class);
        $testAdapter->method('isUserPublishEnabled')->willReturn(false);
        $testAdapter->method('getAdapterName')->willReturn("UnitTest");
        app()->instance(H5PAdapterInterface::class, $testAdapter);

        $this->withSession(['authId' => $authId])
            ->post(route('article.store'), [
                'title' => "Title",
                'content' => '<section class=" ndla-section"><math display="block"><mrow><mmultiscripts><mi>F</mi><mn>3</mn><none/><mprescripts/><mn>2</mn><none/></mmultiscripts></mrow></math></section>',
                'license' => 'PRIVATE',
            ])
            ->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('articles', [
            'title' => 'Title',
            'content' => '<section class="ndla-section"><math display="block"><mrow><mmultiscripts><mi>F</mi><mn>3</mn><none><mprescripts><mn>2</mn><none></mmultiscripts></mrow></math></section>',
            'is_published' => 1,
            'license' => 'PRIVATE',
        ]);
    }

    public function testCreateAndEditArticleWithIframeContent()
    {
        $this->setupVersion();
        Event::fake();
        $authId = Str::uuid();

        $testAdapter = $this->createStub(H5PAdapterInterface::class);
        $testAdapter->method('isUserPublishEnabled')->willReturn(false);
        $testAdapter->method('getAdapterName')->willReturn("UnitTest");
        app()->instance(H5PAdapterInterface::class, $testAdapter);

        $this->withSession(['authId' => $authId])
            ->post(route('article.store'), [
                'title' => "Title",
                'content' => '<section class=" ndla-section"><header class=" ndla-header"><h1 class=" ndla-h1">Overskrift </h1></header></section><section class="ndla-introduction ndla-section">Innhold</section><section class=" ndla-section"><iframe src="https://www.youtube.com/embed/RAbVTreF3lA" width="560" height="315" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen="allowfullscreen" class="oerlearningorg_resource ndla-iframe"></iframe></section>',
                'is_published' => 1,
                'license' => 'PRIVATE',
            ])
            ->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('articles', [
            'title' => 'Title',
            'content' => '<section class="ndla-section"><header class="ndla-header"><h1 class="ndla-h1">Overskrift </h1></header></section><section class="ndla-introduction ndla-section">Innhold</section><section class="ndla-section"><iframe src="https://www.youtube.com/embed/RAbVTreF3lA" width="560" height="315" allowfullscreen class="oerlearningorg_resource ndla-iframe"></iframe></section>',
            'is_published' => 1,
            'license' => 'PRIVATE',
        ]);

        $this->put(route('article.update', Article::first()), [
            'title' => "Updated title",
            'content' => '<section class=" ndla-section"><header class=" ndla-header"><h1 class="ndla-h1">Mer om forenkling av rasjonale uttrykk </h1></header></section><section class="ndla-introduction ndla-section">Hvordan skal vi trekke sammen (addere og subtrahere) rasjonale uttrykk som ogs?? inneholder andregradsuttrykk?</section><section class="ndla-section"><iframe src="https://www.youtube.com/embed/RAbVTreF3lA" width="560" height="315" allowfullscreen class="oerlearningorg_resource ndla-iframe"></iframe></section>',
            'is_published' => 1,
            'license' => 'BY-NC-ND',
        ])
            ->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('articles', [
            'title' => 'Updated title',
            'content' => '<section class="ndla-section"><header class="ndla-header"><h1 class="ndla-h1">Mer om forenkling av rasjonale uttrykk </h1></header></section><section class="ndla-introduction ndla-section">Hvordan skal vi trekke sammen (addere og subtrahere) rasjonale uttrykk som ogs?? inneholder andregradsuttrykk?</section><section class="ndla-section"><iframe src="https://www.youtube.com/embed/RAbVTreF3lA" width="560" height="315" allowfullscreen class="oerlearningorg_resource ndla-iframe"></iframe></section>',
            'is_published' => 1,
            'license' => 'BY-NC-ND',
        ]);
    }

    public function testEditArticle()
    {
        $this->setupVersion();
        $this->setupAuthApi([
            'getUser' => new User("1", "this", "that", "this@that.com")
        ]);
        Event::fake();
        $authId = Str::uuid();
        $article = Article::factory()->create([
            'owner_id' => $authId,
            'is_published' => 1,
            'license' => 'BY',
        ]);

        $testAdapter = $this->createStub(H5PAdapterInterface::class);
        $testAdapter->method('isUserPublishEnabled')->willReturn(false);
        $testAdapter->method('getAdapterName')->willReturn("UnitTest");
        app()->instance(H5PAdapterInterface::class, $testAdapter);

        $this->withSession(['authId' => $authId])
            ->put(route('article.update', $article->id), [
                'title' => "Title",
                'content' => "Content",
                'license' => 'BY-NC',
            ])->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas('articles', [
            'title' => 'Title',
            'content' => 'Content',
            'is_published' => 1,
            'license' => 'BY-NC',
        ]);

        $newArticle = Article::where('title', "Title")
            ->where('content', "Content")
            ->where('is_published', 1)
            ->first();

        $this->get(route('article.show', $newArticle->id))
            ->assertSee($newArticle->title)
            ->assertSee($newArticle->content);
    }

    public function testEditArticleWithDraftEnabled()
    {
        $this->setupVersion();
        $this->setupAuthApi([
            'getUser' => new User("1", "this", "that", "this@that.com")
        ]);

        $this->mockH5pLti();
        $testAdapter = $this->createStub(H5PAdapterInterface::class);
        $testAdapter->method('isUserPublishEnabled')->willReturn(true);
        $testAdapter->method('getAdapterName')->willReturn("UnitTest");
        app()->instance(H5PAdapterInterface::class, $testAdapter);

        Event::fake();
        $authId = Str::uuid();
        $this->withSession(['authId' => $authId])
            ->post(route('article.store'), [
                'title' => "New article",
                'content' => "New content",
                'requestToken' => Str::uuid(),
                'lti_message_type' => "ltirequest",
                'isPublished' => 0,
                'license' => 'BY',
            ])
            ->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas('articles', [
            'title' => 'New article',
            'content' => 'New content',
            'is_published' => 0,
            'license' => 'BY',
        ]);
        /** @var Article $article */
        $article = Article::where('title', 'New article')->first();
        $this->withSession(['authId' => $authId])
            ->put(route('article.update', $article->id), [
                'title' => "Title",
                'content' => "Content",
                'requestToken' => Str::uuid(),
                'lti_message_type' => "ltirequest",
                'isPublished' => 0,
                'license' => 'BY-ND',
            ])->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas('articles', [
            'title' => 'Title',
            'content' => 'Content',
            'is_published' => 0,
            'license' => 'BY-ND',
        ]);

        $article = Article::where('title', 'Title')->first();
        $this->withSession(['authId' => $authId])
            ->put(route('article.update', $article->id), [
                'title' => "Title",
                'content' => "Content",
                'requestToken' => Str::uuid(),
                'lti_message_type' => "ltirequest",
                'isPublished' => 1,
            ])->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas('articles', ['title' => 'Title', 'content' => 'Content', 'is_published' => 1]);
        $article = Article::where('title', 'Title')
            ->where('content', "Content")
            ->where('is_published', 1)
            ->first();
        $this->get(route('article.show', $article->id))
            ->assertSee($article->title)
            ->assertSee($article->content);
    }

    private function mockH5pLti()
    {
        $h5pLti = $this->getMockBuilder(H5pLti::class)->getMock();
        app()->instance(H5pLti::class, $h5pLti);
    }

    public function testViewArticle()
    {
        $this->setupVersion();
        $article = Article::factory()->create([
            'is_published' => 1,
            'license' => 'BY',
        ]);

        $this->get(route('article.show', $article->id))
            ->assertSee($article->title)
            ->assertSee($article->content);
    }

    public function testMustBeLoggedInToCreateArticle()
    {
        $_SERVER['QUERY_STRING'] = 'forTestingPurposes';
        $this->get(route('article.create'))
            ->assertStatus(Response::HTTP_FOUND);
    }

}
