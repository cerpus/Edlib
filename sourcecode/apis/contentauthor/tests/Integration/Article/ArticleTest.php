<?php

namespace Tests\Integration\Article;

use App\ApiModels\User;
use App\Article;
use App\Events\ArticleWasSaved;
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
    use RefreshDatabase;
    use MockVersioningTrait;
    use MockResourceApi;
    use MockAuthApi;

    public function testRewriteUploadUrls(): void
    {
        $article = Article::factory()->create([
            'content' => '<p>This is an image: <img src="/h5pstorage/article-uploads/foo.jpg"></p>',
        ]);

        $this->assertSame(
            "<p>This is an image: <img src=\"http://localhost/content/assets/article-uploads/foo.jpg\"></p>\n",
            $article->render(),
        );
    }

    public function testLeavesNonUploadUrlsAlone(): void
    {
        $article = Article::factory()->create([
            'content' => '<p>This is an image: <img src="http://example.com/foo.jpg"></p>',
        ]);

        $this->assertSame(
            "<p>This is an image: <img src=\"http://example.com/foo.jpg\"></p>\n",
            $article->render(),
        );
    }

    public function testRendersArticleWithBrokenHtml(): void
    {
        $article = Article::factory()->create([
            'content' => '<div>Foo<b></div>bar</b>',
        ]);

        // libxml works in mysterious ways.
        // We don't really care that the output looks like this, but it's nice
        // to know if it suddenly changes after an update or such anyway.
        $this->assertSame(
            "<div>Foo<b></b><p>bar</p></div>\n",
            $article->render(),
        );
    }

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
            ArticleWasSaved::class,
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
        $this->setupVersion(['getVersion' => false]);
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
            'content' => '<section class=" ndla-section"><header class=" ndla-header"><h1 class="ndla-h1">Mer om forenkling av rasjonale uttrykk </h1></header></section><section class="ndla-introduction ndla-section">Hvordan skal vi trekke sammen (addere og subtrahere) rasjonale uttrykk som også inneholder andregradsuttrykk?</section><section class="ndla-section"><iframe src="https://www.youtube.com/embed/RAbVTreF3lA" width="560" height="315" allowfullscreen class="oerlearningorg_resource ndla-iframe"></iframe></section>',
            'is_published' => 1,
            'license' => 'BY-NC-ND',
        ])
            ->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('articles', [
            'title' => 'Updated title',
            'content' => '<section class="ndla-section"><header class="ndla-header"><h1 class="ndla-h1">Mer om forenkling av rasjonale uttrykk </h1></header></section><section class="ndla-introduction ndla-section">Hvordan skal vi trekke sammen (addere og subtrahere) rasjonale uttrykk som også inneholder andregradsuttrykk?</section><section class="ndla-section"><iframe src="https://www.youtube.com/embed/RAbVTreF3lA" width="560" height="315" allowfullscreen class="oerlearningorg_resource ndla-iframe"></iframe></section>',
            'is_published' => 1,
            'license' => 'BY-NC-ND',
        ]);
    }

    public function testEditArticle()
    {
        $this->setupVersion(['getVersion' => false]);
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

        /** @var Article $newArticle */
        $newArticle = Article::where('title', "Title")
            ->where('content', "Content")
            ->where('is_published', 1)
            ->first();

        $this->get(route('article.show', $newArticle->id))
            ->assertSee($newArticle->title)
            ->assertSee($newArticle->render(), false);
    }

    public function testEditArticleWithDraftEnabled()
    {
        $this->setupVersion(['getVersion' => false]);
        $this->setupAuthApi([
            'getUser' => new User("1", "this", "that", "this@that.com")
        ]);

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

        /** @var Article $article */
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

        /** @var Article $article */
        $article = Article::where('title', 'Title')
            ->where('content', "Content")
            ->where('is_published', 1)
            ->first();
        $this->get(route('article.show', $article->id))
            ->assertSee($article->title)
            ->assertSee($article->render(), false);
    }

    public function testViewArticle()
    {
        $this->setupVersion(['getVersion' => false]);

        $article = Article::factory()->create([
            'is_published' => 1,
            'license' => 'BY',
        ]);

        $this->get(route('article.show', $article->id))
            ->assertSee($article->title)
            ->assertSee($article->render(), false);
    }

    public function testMustBeLoggedInToCreateArticle()
    {
        $this->get(route('article.create'))
            ->assertForbidden();
    }

    public function testRewriteUrls()
    {
        $article = Article::factory()->create([
            'content' => 'This is the original content',
        ]);

        $originalUrl = 'original-url';
        $newUrl = 'new-url';

        $article->content = 'This is the original content with the original URL: ' . $originalUrl;
        $article->save();

        $article->rewriteUrls($originalUrl, $newUrl);

        $this->assertStringNotContainsString($originalUrl, $article->content);
        $this->assertStringContainsString($newUrl, $article->content);
    }

    public function testParent()
    {
        $article = Article::factory()->create();

        $parentArticle = Article::factory()->create();

        $article->parent()->associate($parentArticle);
        $article->save();

        $retrievedParent = $article->parent;

        $this->assertEquals($parentArticle->id, $retrievedParent->id);
    }

    public function testGetISO6393Language()
    {
        $article = Article::factory()->create();

        $language = $article->getISO6393Language();

        $this->assertEquals('eng', $language);
    }

    public function testSetParentVersionId()
    {
        $article = Article::factory()->create([
            'parent_version_id' => 'original_parent_version_id',
        ]);

        $parentVersionId = 'new_parent_version_id';

        $isChanged = $article->setParentVersionId($parentVersionId);

        $this->assertTrue($isChanged);
        $this->assertEquals($parentVersionId, $article->parent_version_id);
    }

    public function testScopeNoMaxScore()
    {
        Article::factory()->create(['max_score' => null]);
        Article::factory()->create(['max_score' => 10]);

        $articles = Article::noMaxScore()->get();

        $this->assertCount(1, $articles);
        $this->assertNull($articles[0]->max_score);
    }

    public function testScopeOfBulkCalculated()
    {
        Article::factory()->create(['bulk_calculated' => 0]);
        Article::factory()->create(['bulk_calculated' => 1]);
        Article::factory()->create(['bulk_calculated' => 2]);

        $articles = Article::ofBulkCalculated(1)->get();

        $this->assertCount(1, $articles);
        $this->assertEquals(1, $articles[0]->bulk_calculated);
    }
}
