<?php

namespace Tests\Integration\Http\Controllers;

use App\H5PContent;
use App\H5PContentLibrary;
use App\H5PContentsMetadata;
use App\H5PContentsUserData;
use App\H5PLibrary;
use App\H5PLibraryLibrary;
use App\Http\Controllers\H5PController;
use App\Http\Libraries\License;
use App\Libraries\H5P\Adapters\CerpusH5PAdapter;
use App\Libraries\H5P\Adapters\NDLAH5PAdapter;
use App\Libraries\H5P\H5PConfigAbstract;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use Generator;
use H5PCore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class H5PControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    #[DataProvider('provider_testCreate')]
    public function testCreate(string $adapterMode, ?string $contentType): void
    {
        $this->session([
            'authId' => $this->faker->uuid(),
            'name' => 'Emily Quackfaster',
            'userName' => 'QuackMaster',
            'email' => 'emily.quackfaster@duckburg.quack',
            'locale' => 'nb-no',
            'adapterMode' => $adapterMode,
        ]);
        $request = Request::create('lti-content/create', 'POST', [
            'redirectToken' => $this->faker->uuid,
        ]);

        H5PLibrary::factory()->create();

        $h5pCore = app(H5pCore::class);

        $articleController = app(H5PController::class);
        $result = $articleController->create($request, $h5pCore, $contentType);

        $this->assertInstanceOf(View::class, $result);

        $data = $result->getData();

        $this->assertNotEmpty($data['config']);
        $this->assertNotEmpty($data['jsScript']);
        $this->assertNotEmpty($data['styles']);
        $this->assertArrayHasKey('libName', $data);
        $this->assertNotEmpty($data['editorSetup']);
        $this->assertNotEmpty($data['state']);
        $this->assertArrayHasKey('configJs', $data);
        $this->assertSame($contentType, $data['libName']);

        $config = json_decode(substr($result['config'], 25, -9), true, flags: JSON_THROW_ON_ERROR);
        if ($contentType === null) {
            $this->assertTrue($config['hubIsEnabled']);
        } else {
            $this->assertFalse($config['hubIsEnabled']);
        }
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
        $this->assertSame($contentType, $state['library']);
        $this->assertNull($state['libraryid']);
        $this->assertSame('nob', $state['language_iso_639_3']);
        $this->assertEquals(config('license.default-license'), $state['license']);
    }

    public static function provider_testCreate(): Generator
    {
        yield 'cerpus-withoutContentType' => ['cerpus', null];
        yield 'ndla-withoutContentType' => ['ndla', null];
        yield 'cerpus-withContentType' => ['cerpus', 'H5P.Toolbar 1.2'];
        yield 'ndla-withContentType' => ['ndla', 'H5P.Toolbar 1.2'];
    }

    #[DataProvider('provider_adapterMode')]
    public function testEdit(string $adapterMode): void
    {
        Session::put('adapterMode', $adapterMode);
        $userId = $this->faker->uuid;
        $this->session([
            'authId' => $userId,
            'name' => 'Emily Quackfaster',
            'userName' => 'QuackMaster',
            'email' => $this->faker->email,
            'locale' => 'nn-no',
        ]);
        $redirectToken = $this->faker->uuid;

        $lib = H5PLibrary::factory()->create([
            'major_version' => 1,
            'minor_version' => 6,
        ]);
        $upgradeLib = H5PLibrary::factory()->create([
            'major_version' => 1,
            'minor_version' => 12,
        ]);

        $h5pContent = H5PContent::factory()->create([
            'user_id' => $this->faker->uuid,
            'library_id' => $lib->id,
            'license' => License::LICENSE_CC,
            'language_iso_639_3' => 'nob',
        ]);

        H5PContentsUserData::factory()->create([
            'content_id' => $h5pContent->id,
            'user_id' => $userId,
            'data' => $this->faker->sentence,
        ]);

        H5PContentsMetadata::factory()->create([
            'content_id' => $h5pContent->id,
            'default_language' => 'nb',
        ]);

        H5PContentLibrary::factory()->create(['content_id' => $h5pContent->id, 'library_id' => $upgradeLib->id]);

        $result = $this->post('/h5p/' . $h5pContent->id . '/edit?redirectToken=' . $redirectToken)
            ->assertOk()
            ->original;
        $this->assertNotEmpty($result);
        $this->assertInstanceOf(View::class, $result);
        $data = $result->getData();

        $this->assertSame($h5pContent->id, $data['id']);
        $this->assertInstanceOf(H5PContent::class, $data['h5p']);
        $this->assertSame($h5pContent->id, $data['h5p']->id);

        $this->assertNotEmpty($data['config']);
        $this->assertNotEmpty($data['adminConfig']);
        $this->assertNotEmpty($data['jsScript']);
        $this->assertNotEmpty($data['styles']);
        $this->assertNotEmpty($data['libName']);
        $this->assertNotEmpty($data['hasUserProgress']);
        $this->assertNotEmpty($data['editorSetup']);
        $this->assertNotEmpty($data['state']);

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
        $this->assertEquals($lib->title . ' 1.6.3', $editorSetup['contentProperties']['type']);
        $this->assertSame(null, $editorSetup['contentProperties']['ownerName']);
        $this->assertSame($upgradeLib->id, $editorSetup['libraryUpgradeList'][0]['id']);
        $this->assertSame('nb', $editorSetup['h5pLanguage']);

        $state = json_decode($data['state'], true, flags: JSON_THROW_ON_ERROR);
        $this->assertEquals(License::LICENSE_CC, $state['license']);
        $this->assertEquals($lib->id, $state['libraryid']);
        $this->assertEquals($h5pContent->language_iso_639_3, $state['language_iso_639_3']);
        $this->assertNotEmpty($state['parameters']);
        $this->assertSame($redirectToken, $state['redirectToken']);
        $this->assertNotEmpty($state['title']);
    }

    #[DataProvider('invalidRequestsProvider')]
    public function testStoreRequiresParameters(array $jsonData, array $errorFields): void
    {
        $this
            ->withSession(['authId' => $this->faker->uuid])
            ->postJson('/h5p', ['_token' => csrf_token(), ...$jsonData])
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errorFields);
    }

    #[DataProvider('invalidRequestsProvider')]
    public function testUpdateRequiresParameters(array $jsonData, array $errorFields): void
    {
        $content = H5PContent::factory()->create();

        $this
            ->withSession(['authId' => $this->faker->uuid])
            ->putJson('/h5p/' . $content->id, [
                '_token' => csrf_token(),
                ...$jsonData,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errorFields);
    }

    public static function invalidRequestsProvider(): iterable
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

    #[DataProvider('provider_adapterMode')]
    public function testShow(string $adapterMode): void
    {
        $this->app->singleton(H5PAdapterInterface::class, match ($adapterMode) {
            'cerpus' => CerpusH5PAdapter::class,
            'ndla' => NDLAH5PAdapter::class,
            default => throw new LogicException('Invalid adapter'),
        });

        Storage::fake('test', ['url' => 'http://localhost/h5pstorage']);
        $resourceId = $this->faker->uuid;

        $depH5PVideo = H5PLibrary::factory()->create(['name' => 'H5P.Video', 'major_version' => 2, 'minor_version' => 9]);
        $depCerpusVideo = H5PLibrary::factory()->create(['name' => 'H5P.CerpusVideo', 'major_version' => 3, 'minor_version' => 8]);
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
        H5PLibraryLibrary::create([
            'library_id' => $library->id,
            'required_library_id' => $depH5PVideo->id,
            'dependency_type' => 'preloaded',
        ]);
        H5PLibraryLibrary::create([
            'library_id' => $library->id,
            'required_library_id' => $depCerpusVideo->id,
            'dependency_type' => 'preloaded',
        ]);

        Storage::put('libraries/H5P.DepLib-2.19/deplib.js', 'Here be JS content');
        Storage::put('libraries/H5P.DepLib-2.19/deplib.css', 'Here be CSS content');
        H5PContent::factory()->create([
            'library_id' => $library->id,
        ]);
        $content = H5PContent::factory()->create([
            'library_id' => $library->id,
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
        H5PContentLibrary::create([
            'content_id' => $content->id,
            'library_id' => $depH5PVideo->id,
            'dependency_type' => 'preloaded',
            'weight' => 1,
            'drop_css' => 0,
        ]);
        H5PContentLibrary::create([
            'content_id' => $content->id,
            'library_id' => $depCerpusVideo->id,
            'dependency_type' => 'preloaded',
            'weight' => 1,
            'drop_css' => 0,
        ]);

        $request = new Oauth1Request('POST', 'http://localhost/h5p/' . $content->id, [
            'lti_message_type' => 'basic-lti-launch-request',
            'ext_embed_id' => $resourceId,
            'resource_link_title' => 'Some resource title',
        ]);
        $request = $this->app->make(SignerInterface::class)->sign(
            $request,
            $this->app->make(CredentialStoreInterface::class),
        );

        $response = $this->post('/h5p/' . $content->id, $request->toArray());
        $result = $response->original;

        $this->assertStringContainsString('<div class="h5p-content" data-content-id="' . $content->id . '"></div>', $response->content());
        $this->assertInstanceOf(View::class, $result);
        $this->assertEquals($content->id, $result['id']);
        $this->assertFalse($result['preview']);
        $this->assertNotEmpty($result['jsScripts']);
        $this->assertNotEmpty($result['styles']);
        $this->assertArrayHasKey('inlineStyle', $result);
        $assetJs = Str::after($result['jsScripts'][0], '/h5pstorage/');
        $assetCss = Str::after($result['styles'][0], '/h5pstorage/');
        Storage::assertExists($assetJs);
        Storage::assertExists($assetCss);
        $this->assertStringContainsString('Here be JS content', Storage::get($assetJs));
        $this->assertStringContainsString('Here be CSS content', Storage::get($assetCss));

        $config = json_decode(substr($result['config'], 25, -9), flags: JSON_THROW_ON_ERROR);
        $this->assertObjectHasProperty('baseUrl', $config);
        $this->assertObjectHasProperty('url', $config);
        if (config('h5p.saveFrequency') === false) {
            $this->assertObjectNotHasProperty('user', $config);
        } else {
            $this->assertObjectHasProperty('user', $config);
        }
        $this->assertObjectHasProperty('tokens', $config);
        $this->assertObjectHasProperty('siteUrl', $config);
        $this->assertObjectHasProperty('l10n', $config);
        $this->assertObjectHasProperty('loadedJs', $config);
        $this->assertObjectHasProperty('loadedCss', $config);
        $this->assertObjectHasProperty('pluginCacheBuster', $config);
        $this->assertObjectHasProperty('libraryUrl', $config);
        $this->assertEquals('/ajax?action=', $config->ajaxPath);
        $this->assertTrue($config->canGiveScore);
        $this->assertStringEndsWith("/s/resources/$resourceId", $config->documentUrl);

        $contents = $config->contents->{"cid-$content->id"};
        $this->assertEquals('H5P.Foobar 1.18', $contents->library);
        $this->assertObjectHasProperty('jsonContent', $contents);
        $this->assertObjectHasProperty('exportUrl', $contents);
        $this->assertObjectHasProperty('embedCode', $contents);
        $this->assertObjectHasProperty('resizeCode', $contents);
        $this->assertObjectHasProperty('displayOptions', $contents);
        $this->assertObjectHasProperty('contentUserData', $contents);
        $this->assertStringContainsString("/s/resources/$resourceId", $contents->embedCode);
        $this->assertStringContainsString('Some resource title', $contents->embedCode);

        // Adapter specific
        $this->assertContains('//cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-svg.js', $result['jsScripts']);

        if ($adapterMode === "ndla") {
            $this->assertContains('//cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-svg.js', $result['jsScripts']);
            $this->assertContains('/js/h5peditor-custom.js', $result['jsScripts']);

            $this->assertContains('/css/ndlah5p-iframe-legacy.css?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $result['styles']);
            $this->assertContains('/css/ndlah5p-iframe.css?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $result['styles']);
        }
    }

    public static function provider_adapterMode(): Generator
    {
        yield 'cerpus' => ['cerpus'];
        yield 'ndla' => ['ndla'];
    }
}
