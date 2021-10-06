<?php

use App\User;
use App\Article;
use App\H5PContent;
use Tests\TestCase;
use App\OauthVerifier;
use App\H5PContentLibrary;
use Tests\Traits\VersionedH5PTrait;
use Tests\Traits\MockLicensingTrait;
use Cerpus\VersionClient\VersionData;
use Illuminate\Support\Facades\Storage;
use Tests\Traits\VersionedArticleTrait;
use App\Listeners\Article\HandleVersioning;
use App\Listeners\Article\Copy\HandleLicensing;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentCopyTest extends TestCase
{
    use VersionedH5PTrait;
    use RefreshDatabase;
    use MockLicensingTrait;
    use VersionedArticleTrait;

    public function tearDown(): void
    {
        $this->removeDirectories();
    }

    public function testCommonErrorsAppear()
    {
        \Event::fake();
        $this->withoutMiddleware();

        $user = factory(User::class)->make();
        $article = factory(Article::class)->create(['owner_id' => $user->auth_id]);
        $this->assertCount(1, Article::all());

        //Test required params
        $this->post(route('content.copy', [
            'auth_id' => '1',
        ]))->assertStatus(302);

        $this->app->instance('requestId', 123);

        // Test copy non existing article
        $this->post(route('content.copy', [
            'id' => $article->id . '1',
            'auth_id' => '1',
        ]))->assertStatus(404);
    }

    /**
     *
     * @return void
     */
    public function testCopyArticle()
    {

        $this->withoutMiddleware();
        $storage = Storage::disk('article-uploads');

        // https://adamwathan.me/2016/08/15/3-approaches-to-testing-events-in-laravel/
        $versioningListener = Mockery::spy(HandleVersioning::class);
        app()->instance(HandleVersioning::class, $versioningListener);

        $licenseingListener = Mockery::spy(HandleLicensing::class);
        app()->instance(HandleLicensing::class, $licenseingListener);

        $user = factory(User::class)->make();
        $this->setUpOriginalArticle(['owner_id' => $user->auth_id], 'PRIVATE', false);

        // Test a real copy, by the owner
        $this->assertCount(1, Article::all());
        $this->assertCount(1, $storage->directories());

        $this->response = $this->post(route('content.copy', [
            'id' => $this->originalArticle->id,
            'auth_id' => $user->auth_id,
        ]))
            ->assertJson([
                'created' => true,
            ])
            ->assertStatus(201);

        $this->assertCount(2, Article::all());
        $this->assertCount(2, $storage->directories());


        $theResponse = json_decode($this->response->getContent());
        $newArticle = Article::find($theResponse->id);

        $this->assertEquals($user->auth_id, $newArticle->owner_id);
        $this->assertNotEquals($newArticle->version_id, $this->originalArticle->version_id);

        $versioningListener->shouldHaveReceived('handle')->with(Mockery::on(function ($event) {
            return $event->reason === VersionData::COPY;
        }))->once();

        $licenseingListener->shouldHaveReceived('handle')->with(Mockery::on(function ($event) use ($newArticle) {
            return $event->article->id === $newArticle->id;
        }))->once();

    }

    public function testCopyH5P()
    {
        \Event::fake();

        $this->withoutMiddleware();
        $storage = Storage::disk('h5p-uploads');

        $user = factory(User::class)->make();
        $originalDirectoryCount = count($storage->directories('content'));
        $h5p = $this->setUpOriginalH5P(['user_id' => $user->auth_id], 'PRIVATE', false);
        $this->assertCount(1, H5PContent::all());
        $this->assertCount($originalDirectoryCount + 1, $storage->directories('content'));
        $this->assertCount(1, $this->originalH5P->contentLibraries);
        $this->assertCount(1, H5PContentLibrary::all());

        $this->response = $this->post(route('content.copy', [
            'id' => $h5p->id,
            'auth_id' => $user->auth_id,
        ]))
            ->assertJson([
                'created' => true,
            ])
            ->assertStatus(201);

        $this->assertCount(2, H5PContent::all());
        $this->assertCount($originalDirectoryCount + 2, $storage->directories('content'));


        $theResponse = json_decode($this->response->getContent());
        $newH5P = H5PContent::find($theResponse->id);
        $this->assertCount(1, $newH5P->contentLibraries);
        $this->assertCount(2, H5PContentLibrary::all());
        $this->assertEquals($user->auth_id, $newH5P->user_id);
        $this->assertEquals($this->originalH5P->license, $newH5P->license);
    }

    public function testAccessRights()
    {
        \Event::fake();
        $this->withoutMiddleware();
        $user = factory(User::class)->make();

        $this->setUpOriginalArticle(['owner_id' => $user->auth_id], 'PRIVATE', false);
        // No one but the user should be able to copy this article
        $this->post(route('content.copy', [
            'id' => $this->originalArticle->id,
            'auth_id' => '1',
        ]))
            ->assertJson([
                'created' => false,
            ])
            ->assertStatus(403);


        $this->setUpOriginalArticle(['owner_id' => $user->auth_id], 'BY', true);
        $this->post(route('content.copy', [
            'id' => $this->originalArticle->id,
            'auth_id' => $user->auth_id,
        ]))
            ->assertJson([
                'created' => true,
            ])
            ->assertStatus(201);

        $this->setUpOriginalH5P(['user_id' => $user->auth_id], 'PRIVATE', false);
        $this->post(route('content.copy', [
            'id' => $this->originalH5P->id,
            'auth_id' => '1',
        ]))
            ->assertJson([
                'created' => false,
            ])
            ->assertStatus(403);

        $this->setUpOriginalH5P(['user_id' => $user->auth_id], 'BY', true);
        $this->post(route('content.copy', [
            'id' => $this->originalH5P->id,
            'auth_id' => $user->auth_id,
        ]))
            ->assertJson([
                'created' => true,
            ])
            ->assertStatus(201);


    }

    public function testOauthSigning()
    {
        \Event::fake();
        $user = factory(User::class)->make();
        $this->setUpOriginalH5P(['user_id' => $user->auth_id], 'BY', true);
        $this->post(route('content.copy', [
            'id' => $this->originalH5P->id,
            'auth_id' => $user->auth_id,
            'oauth_consumer_key' => config('app.consumer-key'),
            'oauth_nonce' => 'kYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => 1318622958,
            'oauth_token' => '370773112-GmHxMAgYyLbNEtIKZeRNFsMKPR9EyMZeS9weJAEb',
        ]))
            ->assertStatus(401);

        $params = [
            'id' => $this->originalH5P->id,
            'auth_id' => $user->auth_id,
            'oauth_consumer_key' => config('app.consumer-key'),
            'oauth_nonce' => 'kYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_signature' => 'kYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg',
            'oauth_timestamp' => 1318622958,
            'oauth_version' => 1.0
        ];

        $oauth = new OauthVerifier(config('app.consumer-secret'));
        $uri = url(route('content.copy'));
        $expectedSignature = $oauth->generateSignature('POST', $uri, $params);

        $params['oauth_signature'] = $expectedSignature;

        $this->post(route('content.copy', $params))
            ->assertStatus(201)
            ->assertJson([
                'created' => true,
            ]);
    }
}
