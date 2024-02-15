<?php

namespace Tests\Integration\Http\Controllers;

use App\ApiModels\User;
use App\H5PCollaborator;
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
use Tests\Helpers\MockAuthApi;
use Tests\TestCase;

class H5PControllerTest extends TestCase
{
    use RefreshDatabase;
    use MockAuthApi;
    use WithFaker;

    /** @dataProvider provider_testCreate */
    public function testCreate(string $adapterMode, ?string $contentType): void
    {
        config([
            "h5p.default-resource-language" => 'fi',
        ]);

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
        $this->assertArrayHasKey('emails', $data);
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
        $this->assertSame('nb', $config['locale']);
        $this->assertSame('nb', $config['localeConverted']);
        $this->assertSame('nb', $config['editor']['language']);
        $this->assertSame('fi', $config['editor']['defaultLanguage']);

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
        $this->assertSame($contentType, $state['library']);
        $this->assertNull($state['libraryid']);
        $this->assertSame('nob', $state['language_iso_639_3']);
        $this->assertEquals(config('license.default-license'), $state['license']);

        // Adapter specific
        if ($adapterMode === 'ndla') {
            $this->assertContains('/js/react-contentbrowser.js', $result['configJs']);
        } elseif ($adapterMode === 'cerpus') {
            $this->assertSame([], $data['configJs']);
        }
    }

    public function provider_testCreate(): Generator
    {
        yield 'cerpus-withoutContentType' => ['cerpus', null];
        yield 'ndla-withoutContentType' => ['ndla', null];
        yield 'cerpus-withContentType' => ['cerpus', 'H5P.Toolbar 1.2'];
        yield 'ndla-withContentType' => ['ndla', 'H5P.Toolbar 1.2'];
    }

    /** @dataProvider provider_adapterMode */
    public function testEdit(string $adapterMode): void
    {
        config([
            "h5p.default-resource-language" => 'fi',
        ]);
        Session::put('adapterMode', $adapterMode);
        $user = new User($this->faker->uuid, 'Emily', 'Quackfaster', 'emily.quackfaster@duckburg.quack');
        $this->setupAuthApi([
            'getUser' => $user,
        ]);
        $this->session([
            'authId' => $this->faker->uuid(),
            'name' => 'Emily Quackfaster',
            'userName' => 'QuackMaster',
            'email' => $user->getEmail(),
            'locale' => 'nn-no',
        ]);
        $request = Request::create('lti-content/create', 'POST', [
            'redirectToken' => $this->faker->uuid,
        ]);

        $lib = H5PLibrary::factory()->create([
            'major_version' => 1,
            'minor_version' => 6,
        ]);
        $upgradeLib = H5PLibrary::factory()->create([
            'major_version' => 1,
            'minor_version' => 12,
        ]);

        $h5pContent = H5PContent::factory()->create([
            'user_id' => $user->getId(),
            'library_id' => $lib->id,
            'license' => License::LICENSE_CC,
            'language_iso_639_3' => 'nob',
        ]);

        H5PContentsUserData::factory()->create([
            'content_id' => $h5pContent->id,
            'user_id' => $user->getId(),
            'data' => $this->faker->sentence,
        ]);

        H5PContentsMetadata::factory()->create([
            'content_id' => $h5pContent->id,
            'default_language' => 'nb',
        ]);

        H5PContentLibrary::factory()->create(['content_id' => $h5pContent->id, 'library_id' => $upgradeLib->id]);

        if ($adapterMode === 'cerpus') {
            H5PCollaborator::factory()->create([
                'h5p_id' => $h5pContent->id,
                'email' => 'dd@duckburg.quack',
            ]);
        }

        $articleController = app(H5PController::class);
        $result = $articleController->edit($request, $h5pContent->id);
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
        $this->assertSame($adapterMode === 'cerpus' ? 'dd@duckburg.quack' : '', $data['emails']);
        $this->assertNotEmpty($data['hasUserProgress']);
        $this->assertNotEmpty($data['editorSetup']);
        $this->assertNotEmpty($data['state']);

        $config = json_decode(substr($result['config'], 25, -9), true, flags: JSON_THROW_ON_ERROR);
        $this->assertFalse($config['canGiveScore']);
        $this->assertFalse($config['hubIsEnabled']);
        $this->assertEmpty($config['contents']);
        $this->assertSame('nn', $config['locale']);
        $this->assertSame('nn', $config['localeConverted']);
        $this->assertSame('nn', $config['editor']['language']);
        $this->assertSame('fi', $config['editor']['defaultLanguage']);

        $this->assertNotEmpty($config['core']['scripts']);
        $this->assertNotEmpty($config['core']['styles']);
        $this->assertNotEmpty($config['editor']['assets']['js']);
        $this->assertNotEmpty($config['editor']['assets']['css']);
        $this->assertNotEmpty($config['editor']['copyrightSemantics']);
        $this->assertNotEmpty($config['editor']['metadataSemantics']);
        $this->assertNotEmpty($config['editor']['ajaxPath']);

        $editorSetup = json_decode($data['editorSetup'], true, flags: JSON_THROW_ON_ERROR);
        $this->assertEquals($lib->title . ' 1.6.3', $editorSetup['contentProperties']['type']);
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

        // Adapter specific
        if ($adapterMode === 'ndla') {
            $this->assertContains('/js/react-contentbrowser.js', $result['configJs']);
        } elseif ($adapterMode === 'cerpus') {
            $this->assertSame([], $data['configJs']);
        }
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

    /** @dataProvider provider_adapterMode */
    public function testDoShow(string $adapterMode): void
    {
        $this->app->singleton(H5PAdapterInterface::class, match ($adapterMode) {
            'cerpus' => CerpusH5PAdapter::class,
            'ndla' => NDLAH5PAdapter::class,
            default => throw new LogicException('Invalid adapter'),
        });

        Storage::fake('test');
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

        $result = $this->post('/h5p/' . $content->id, $request->toArray())->original;

        $this->assertInstanceOf(View::class, $result);
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
        if (config('h5p.saveFrequency') === false) {
            $this->assertObjectNotHasAttribute('user', $config);
        } else {
            $this->assertObjectHasAttribute('user', $config);
        }
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
        $this->assertStringContainsString('Some resource title', $contents->embedCode);

        // Adapter specific
        $this->assertContains('//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.9/MathJax.js?config=TeX-AMS-MML_SVG', $result['jsScripts']);
        $this->assertContains('/js/videos/brightcove.js', $result['jsScripts']);

        if ($adapterMode === "ndla") {
            $this->assertContains('/js/h5p/wiris/view.js', $result['jsScripts']);
            $this->assertContains('/js/h5peditor-custom.js', $result['jsScripts']);

            $this->assertContains('/css/ndlah5p-iframe-legacy.css?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $result['styles']);
            $this->assertContains('/css/ndlah5p-iframe.css?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $result['styles']);
        } elseif ($adapterMode === "cerpus") {
            $this->assertContains('/js/videos/streamps.js', $result['jsScripts']);
        }
    }

    public function provider_adapterMode(): Generator
    {
        yield 'cerpus' => ['cerpus'];
        yield 'ndla' => ['ndla'];
    }
}
