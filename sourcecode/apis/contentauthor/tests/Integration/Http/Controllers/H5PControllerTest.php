<?php

namespace Tests\Integration\Http\Controllers;

use App\ApiModels\Resource;
use App\ApiModels\User;
use App\Apis\ResourceApiService;
use App\H5PContent;
use App\H5PContentLibrary;
use App\H5PContentsMetadata;
use App\H5PContentsUserData;
use App\H5PLibrary;
use App\Http\Controllers\H5PController;
use App\Http\Libraries\License;
use Faker\Factory;
use H5PCore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Tests\Helpers\MockAuthApi;
use Tests\TestCase;

class H5PControllerTest extends TestCase
{
    use RefreshDatabase;
    use MockAuthApi;

    public function testCreate(): void
    {
        $faker = Factory::create();
        $this->session([
            'authId' => $faker->uuid(),
            'name' => 'Emily Quackfaster',
            'userName' => 'QuackMaster',
            'email' => 'emily.quackfaster@duckburg.quack',
            'locale' => 'nb-no',
            'jwtToken' => [
                'raw' => 'a unique token',
            ],
        ]);
        $request = Request::create('lti-content/create', 'POST', [
            'redirectToken' => $faker->uuid,
        ]);

        /** @var H5PCore $h5pCore */
        $h5pCore = app(H5pCore::class);
        /** @var H5PController $articleController */
        $articleController = app(H5PController::class);
        $result = $articleController->create($request, $h5pCore);
        $this->assertInstanceOf(View::class, $result);

        $data = $result->getData();

        $this->assertSame('a unique token', $data['jwtToken']);
        $this->assertNotEmpty($data['config']);
        $this->assertNotEmpty($data['jsScript']);
        $this->assertNotEmpty($data['styles']);
        $this->assertArrayHasKey('emails', $data);
        $this->assertArrayHasKey('libName', $data);
        $this->assertNotEmpty($data['editorSetup']);
        $this->assertNotEmpty($data['state']);
        $this->assertArrayHasKey('configJs', $data);

        $config = json_decode(substr($result['config'], 25, -9), true, flags: JSON_THROW_ON_ERROR);
        $this->assertTrue($config['hubIsEnabled']);
        $this->assertEmpty($config['contents']);
        $this->assertSame('nb-no', $config['locale']);
        $this->assertSame('nb', $config['localeConverted']);
        $this->assertSame('nb', $config['editor']['language']);
        $this->assertSame('en', $config['editor']['defaultLanguage']);

        $this->assertNotEmpty($config['core']['scripts']);
        $this->assertNotEmpty($config['core']['styles']);
        $this->assertNotEmpty($config['editor']['assets']['js']);
        $this->assertNotEmpty($config['editor']['assets']['css']);
        $this->assertNotEmpty($config['editor']['copyrightSemantics']);
        $this->assertNotEmpty($config['editor']['metadataSemantics']);
        $this->assertNotEmpty($config['editor']['ajaxPath']);

        $editorSetup = json_decode($data['editorSetup'], true, flags: JSON_THROW_ON_ERROR);
        $this->assertEquals('nb-no', $editorSetup['editorLanguage']);
        $this->assertEquals('nb', $editorSetup['h5pLanguage']);
        $this->assertEquals('Emily Quackfaster', $editorSetup['creatorName']);

        $state = json_decode($data['state'], true, flags: JSON_THROW_ON_ERROR);
        $this->assertNull($state['id']);
        $this->assertNull($state['title']);
        $this->assertFalse($state['isPublished']);
        $this->assertNull($state['library']);
        $this->assertNull($state['libraryid']);
        $this->assertSame('nob', $state['language_iso_639_3']);
        $this->assertEquals(config('license.default-license'), $state['license']);
    }

    public function testEdit(): void
    {
        $faker = Factory::create();
        $user = new User(42, 'Emily', 'Quackfaster', 'emily.quackfaster@duckburg.quack');
        $this->setupAuthApi([
            'getUser' => $user,
        ]);
        $this->session([
            'authId' => $faker->uuid(),
            'name' => 'Emily Quackfaster',
            'userName' => 'QuackMaster',
            'email' => $user->getEmail(),
            'locale' => 'nn-no',
            'jwtToken' => [
                'raw' => 'a unique token',
            ],
        ]);
        $request = Request::create('lti-content/create', 'POST', [
            'redirectToken' => $faker->uuid,
        ]);

        /** @var H5PLibrary $lib */
        $lib = H5PLibrary::factory()->create([
            'major_version' => 1,
            'minor_version' => 6,
        ]);
        /** @var H5PLibrary $upgradeLib */
        $upgradeLib = H5PLibrary::factory()->create([
            'major_version' => 1,
            'minor_version' => 12,
        ]);

        /** @var H5PContent $h5pContent */
        $h5pContent = H5PContent::factory()->create([
            'user_id' => $user->getId(),
            'library_id' => $lib->id,
            'license' => License::LICENSE_CC,
            'language_iso_639_3' => 'nob',
        ]);

        H5PContentsUserData::factory()->create([
            'content_id' => $h5pContent->id,
            'user_id' => $user->getId(),
            'data' => $faker->sentence,
        ]);

        H5PContentsMetadata::factory()->create([
            'content_id' => $h5pContent->id,
            'default_language' => 'nb',
        ]);

        H5PContentLibrary::factory()->create(['content_id' => $h5pContent->id, 'library_id' => $upgradeLib->id]);

        /** @var H5PController $articleController */
        $articleController = app(H5PController::class);
        $result = $articleController->edit($request, $h5pContent->id);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(View::class, $result);
        $data = $result->getData();

        $this->assertSame('a unique token', $data['jwtToken']);
        $this->assertSame($h5pContent->id, $data['id']);
        $this->assertSame($h5pContent->id, $data['h5p']->id);

        $this->assertNotEmpty($data['config']);
        $this->assertNotEmpty($data['adminConfig']);
        $this->assertNotEmpty($data['jsScript']);
        $this->assertNotEmpty($data['styles']);
        $this->assertNotEmpty($data['libName']);
        $this->assertSame('', $data['emails']);
        $this->assertNotEmpty($data['hasUserProgress']);
        $this->assertNotEmpty($data['editorSetup']);
        $this->assertNotEmpty($data['state']);
        $this->assertSame([], $data['configJs']);

        $config = json_decode(substr($result['config'], 25, -9), true, flags: JSON_THROW_ON_ERROR);
        $this->assertFalse($config['canGiveScore']);
        $this->assertFalse($config['hubIsEnabled']);
        $this->assertEmpty($config['contents']);
        $this->assertSame('nn-no', $config['locale']);
        $this->assertSame('nn', $config['localeConverted']);
        $this->assertSame('nn', $config['editor']['language']);
        $this->assertSame('en', $config['editor']['defaultLanguage']);

        $this->assertNotEmpty($config['core']['scripts']);
        $this->assertNotEmpty($config['core']['styles']);
        $this->assertNotEmpty($config['editor']['assets']['js']);
        $this->assertNotEmpty($config['editor']['assets']['css']);
        $this->assertNotEmpty($config['editor']['copyrightSemantics']);
        $this->assertNotEmpty($config['editor']['metadataSemantics']);
        $this->assertNotEmpty($config['editor']['ajaxPath']);

        $editorSetup = json_decode($data['editorSetup'], true, flags: JSON_THROW_ON_ERROR);
        $this->assertEquals('Emily Quackfaster', $editorSetup['contentProperties']['ownerName']);
        $this->assertSame($upgradeLib->id, $editorSetup['libraryUpgradeList'][0]['id']);
        $this->assertSame('nb', $editorSetup['h5pLanguage']);

        $state = json_decode($data['state'], true, flags: JSON_THROW_ON_ERROR);
        $this->assertEquals(License::LICENSE_CC, $state['license']);
        $this->assertEquals($lib->id, $state['libraryid']);
        $this->assertEquals($h5pContent->language_iso_639_3, $state['language_iso_639_3']);
        $this->assertNotEmpty($state['parameters']);
        $this->assertNotEmpty($state['redirectToken']);
        $this->assertNotEmpty($state['title']);
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
        Storage::fake('test');
        $resourceId = $faker->uuid;
        $resourceApi = $this->createMock(ResourceApiService::class);
        $this->instance(ResourceApiService::class, $resourceApi);

        H5PLibrary::factory()->create();
        /** @var H5PLibrary $library */
        $library = H5PLibrary::factory()->create([
            'minor_version' => 18,
        ]);
        $dependency = H5PLibrary::factory()->create([
            'name' => 'H5P.DepLib',
            'major_version' => 2,
            'minor_version' => 19,
            'runnable' => false,
            'preloaded_js' => 'deplib.js',
            'preloaded_css' => 'deplib.css',
        ]);
        Storage::put('libraries/H5P.DepLib-2.19/deplib.js', 'Here be JS content');
        Storage::put('libraries/H5P.DepLib-2.19/deplib.css', 'Here be CSS content');
        H5PContent::factory()->create([
            'library_id' => $library->id,
        ]);
        /** @var H5PContent $content */
        $content = H5PContent::factory()->create([
            'library_id' => $library->id,
            'is_published' => true,
            'is_draft' => false,
            'max_score' => 42,
        ]);
        H5PContentLibrary::create([
            'content_id' => $content->id,
            'library_id' => $dependency->id,
            'dependency_type' => 'preloaded',
            'weight' => 1,
            'drop_css' => 0,
        ]);

        $resourceApi
            ->expects($this->atLeastOnce())
            ->method('getResourceFromExternalReference')
            ->willReturn(new Resource($resourceId, '', '', '', '', '', $content->title));

        $controller = app(H5PController::class);
        $result = $controller->doShow($content->id, $faker->sha1, false)->getData();

        $this->assertEquals($content->id, $result['id']);
        $this->assertFalse($result['preview']);
        $this->assertStringContainsString('data-content-id="'.$content->id.'"', $result['embed']);
        $this->assertNotEmpty($result['jsScripts']);
        $this->assertNotEmpty($result['styles']);
        $this->assertArrayHasKey('inlineStyle', $result);
        $assetJs = Str::after($result['jsScripts'][0], '/content/assets/');
        $assetCss = Str::after($result['styles'][0], '/content/assets/');
        Storage::assertExists($assetJs);
        Storage::assertExists($assetCss);
        $this->assertStringContainsString('Here be JS content', Storage::get($assetJs));
        $this->assertStringContainsString('Here be CSS content', Storage::get($assetCss));

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
        $this->assertObjectHasAttribute('jsonContent', $contents);
        $this->assertObjectHasAttribute('exportUrl', $contents);
        $this->assertObjectHasAttribute('embedCode', $contents);
        $this->assertObjectHasAttribute('resizeCode', $contents);
        $this->assertObjectHasAttribute('displayOptions', $contents);
        $this->assertObjectHasAttribute('contentUserData', $contents);
        $this->assertStringContainsString("/s/resources/$resourceId", $contents->embedCode);
    }
}
