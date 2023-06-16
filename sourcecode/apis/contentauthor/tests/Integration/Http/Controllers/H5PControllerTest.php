<?php

namespace Tests\Integration\Http\Controllers;

use App\ApiModels\Resource;
use App\ApiModels\User;
use App\Apis\ResourceApiService;
use App\H5PContent;
use App\H5PContentLibrary;
use App\H5PLibrary;
use App\Http\Controllers\H5PController;
use App\Http\Libraries\License;
use App\Http\Requests\H5PStorageRequest;
use Faker\Factory;
use Faker\Provider\Uuid;
use H5PCore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Tests\Helpers\MockAuthApi;
use Tests\TestCase;

class H5PControllerTest extends TestCase
{
    use RefreshDatabase;
    use MockAuthApi;

    public function testCreate(): void
    {
        $this->session([
            'authId' => Uuid::uuid(),
        ]);
        $request = new H5PStorageRequest([], [
            'lti_version' => 'LTI-1p0',
            'lti_message_type' => 'basic-lti-launch-request',
            'resource_link_id' => 'random_link_9364f20a-a9b5-411a-8f60-8a4050f85d91',
            'launch_presentation_return_url' => "https://api.edlib.local/lti/v2/editors/contentauthor/return",
            'ext_user_id' => "1",
            'launch_presentation_locale' => "nb",
        ]);

        /** @var H5PCore $h5pCore */
        $h5pCore = app(H5pCore::class);
        /** @var H5PController $articleController */
        $articleController = app(H5PController::class);
        $result = $articleController->create($request, $h5pCore);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(View::class, $result);
        $data = $result->getData();
        $this->assertArrayHasKey('state', $data);
        $state = json_decode($data['state'], true);
        $this->assertEquals(config('license.default-license'), $state['license']);
    }

    public function testEdit(): void
    {
        $this->session([
            'authId' => Uuid::uuid(),
        ]);
        $user = new User(42, 'Emily', 'Quackfaster', 'emily.quackfaster@duckburg.quack');
        $this->setupAuthApi([
            'getUser' => $user,
        ]);

        /** @var H5PLibrary[] $libs */
        $libs = H5PLibrary::factory()->count(3)->create();

        /** @var H5PContent $h5pContent */
        $h5pContent = H5PContent::factory()->create([
            'user_id' => $user->getId(),
            'library_id' => $libs[0]->id,
            'license' => License::LICENSE_CC,
        ]);

        /** @var H5PContentLibrary[] $h5pContentLibs */
        H5PContentLibrary::factory()->create(['content_id' => $h5pContent->id, 'library_id' => $libs[1]->id]);
        H5PContentLibrary::factory()->create(['content_id' => $h5pContent->id, 'library_id' => $libs[2]->id]);

        $this->assertDatabaseHas('h5p_contents', ['id' => $h5pContent->id, 'library_id' => $libs[0]->id]);
        $this->assertDatabaseCount('h5p_libraries', 3);
        $this->assertDatabaseHas('h5p_contents_libraries', ['content_id' => $h5pContent->id, 'library_id' => $libs[1]->id]);
        $this->assertDatabaseHas('h5p_contents_libraries', ['content_id' => $h5pContent->id, 'library_id' => $libs[2]->id]);

        $request = new Request([], [
            'lti_version' => 'LTI-1p0',
            'lti_message_type' => 'basic-lti-launch-request',
            'resource_link_id' => 'random_link_9364f20a-a9b5-411a-8f60-8a4050f85d91',
            'launch_presentation_return_url' => "https://api.edlib.local/lti/v2/editors/contentauthor/return",
            'ext_user_id' => "1",
            'launch_presentation_locale' => "nb",
        ]);

        /** @var H5PController $articleController */
        $articleController = app(H5PController::class);
        $result = $articleController->edit($request, $h5pContent->id);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(View::class, $result);
        $data = $result->getData();
        $this->assertArrayHasKey('state', $data);
        $state = json_decode($data['state'], true);
        $this->assertEquals(License::LICENSE_CC, $state['license']);
    }

    /**
     * @dataProvider invalidRequestsProvider
     */
    public function testStoreRequiresParameters(array $jsonData, array $errorFields): void
    {
        $this
            ->withAuthenticated($this->makeAuthUser())
            ->postJson('/h5p', ['_token' => csrf_token(), ...$jsonData])
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errorFields);
    }

    /**
     * @dataProvider invalidRequestsProvider
     */
    public function testUpdateRequiresParameters(array $jsonData, array $errorFields): void
    {
        $content = H5PContent::factory()->create();

        $this
            ->withAuthenticated($this->makeAuthUser())
            ->putJson('/h5p/'.$content->id, [
                '_token' => csrf_token(),
                ...$jsonData,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errorFields);
    }

    public function invalidRequestsProvider(): iterable
    {
        yield [[], ['title', 'parameters', 'library']];
        yield [[
            'title' => 'Resource title',
            'parameters' => 'invalid json',
            'library' => 'Some Library',
        ], ['parameters']];
        yield [['libraryid' => 999999], ['libraryid']];
        yield [['library' => null], ['library']];
        yield [['language_iso_639_3' => 'eeee'], ['language_iso_639_3']];
    }

    public function testDoShow(): void
    {
        $faker = Factory::create();
        $resourceId = $faker->uuid;
        $resourceApi = $this->createMock(ResourceApiService::class);
        $this->instance(ResourceApiService::class, $resourceApi);

        $faker = Factory::create();
        H5PLibrary::factory()->create();
        /** @var H5PLibrary $library */
        $library = H5PLibrary::factory()->create([
            'minor_version' => 18,
        ]);
        /** @var H5PContent $content */
        H5PContent::factory()->create([
            'library_id' => $library->id,
        ]);
        $content = H5PContent::factory()->create([
            'library_id' => $library->id,
            'is_published' => true,
            'is_draft' => false,
            'max_score' => 42,
        ]);

        $resourceApi
            ->expects($this->exactly(1))
            ->method('getResourceFromExternalReference')
            ->willReturn(new Resource($resourceId, '', '', '', '', $content->title));

        $controller = app(H5PController::class);
        $result = $controller->doShow($content->id, $faker->sha1, false)->getData();

        $this->assertEquals(2, $result['id']);
        $this->assertFalse($result['preview']);
        $this->assertArrayHasKey('embed', $result);
        $this->assertArrayHasKey('config', $result);

        $config = json_decode(substr($result['config'], 25, -9), flags: JSON_THROW_ON_ERROR);

        $this->assertObjectHasAttribute('baseUrl', $config);
        $this->assertObjectHasAttribute('url', $config);
        $this->assertObjectHasAttribute('user', $config);
        $this->assertObjectHasAttribute('tokens', $config);
        $this->assertObjectHasAttribute('siteUrl', $config);
        $this->assertObjectHasAttribute('l10n', $config);
        $this->assertObjectHasAttribute('loadedJs', $config);
        $this->assertObjectHasAttribute('loadedCss', $config);
        $this->assertObjectHasAttribute('pluginCacheBuster', $config);
        $this->assertObjectHasAttribute('libraryUrl', $config);

        $this->assertEquals('/ajax?action=', $config->ajaxPath);
        $this->assertTrue($config->canGiveScore);
        $this->assertStringEndsWith("/s/resources/$resourceId", $config->documentUrl);

        $contents = $config->contents->{"cid-$content->id"};
        $this->assertEquals('H5P.Foobar 1.18', $contents->library);
        $this->assertObjectHasAttribute('exportUrl', $contents);
        $this->assertObjectHasAttribute('embedCode', $contents);
        $this->assertObjectHasAttribute('resizeCode', $contents);
        $this->assertObjectHasAttribute('displayOptions', $contents);
        $this->assertObjectHasAttribute('contentUserData', $contents);
    }
}
