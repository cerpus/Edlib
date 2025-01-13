<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PContent;
use App\H5PContentsMetadata;
use App\H5PLibrary;
use App\H5PLibraryLibrary;
use App\Libraries\DataObjects\BehaviorSettingsDataObject;
use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\H5P\H5PViewConfig;
use App\SessionKeys;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class H5PViewConfigTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    #[DataProvider('provider_adapterMode')]
    public function test_getConfig(string $adapterMode): void
    {
        Session::put('adapterMode', $adapterMode);

        $config = app(H5PViewConfig::class);
        $data = $config->getConfig();

        // Check that the common attributes are present
        $this->assertObjectHasProperty('baseUrl', $data);
        $this->assertObjectHasProperty('url', $data);
        $this->assertObjectHasProperty('postUserStatistics', $data);
        $this->assertObjectHasProperty('ajaxPath', $data);
        if (config('h5p.saveFrequency') === false) {
            $this->assertObjectNotHasProperty('user', $data);
        } else {
            $this->assertObjectHasProperty('user', $data);
        }
        $this->assertObjectHasProperty('canGiveScore', $data);
        $this->assertObjectHasProperty('hubIsEnabled', $data);
        $this->assertObjectHasProperty('ajax', $data);
        $this->assertObjectHasProperty('tokens', $data);
        $this->assertObjectHasProperty('saveFreq', $data);
        $this->assertObjectHasProperty('siteUrl', $data);
        $this->assertObjectHasProperty('l10n', $data);
        $this->assertObjectHasProperty('baseUrl', $data);
        $this->assertObjectHasProperty('loadedJs', $data);
        $this->assertObjectHasProperty('loadedCss', $data);
        $this->assertObjectHasProperty('core', $data);
        $this->assertObjectHasProperty('contents', $data);
        $this->assertObjectHasProperty('crossorigin', $data);
        $this->assertObjectHasProperty('crossoriginRegex', $data);
        $this->assertObjectHasProperty('locale', $data);
        $this->assertObjectHasProperty('localeConverted', $data);
        $this->assertObjectHasProperty('pluginCacheBuster', $data);
        $this->assertObjectHasProperty('libraryUrl', $data);

        // Attributes altered or set
        $this->assertSame('', $data->documentUrl);
        $this->assertSame([], $data->contents);
        $this->assertSame('', $data->ajax['contentUserData']);
        $this->assertSame('/api/progress?action=h5p_setFinished', $data->ajax['setFinished']);
    }

    public static function provider_adapterMode(): Generator
    {
        yield 'cerpus' => ['cerpus'];
        yield 'ndla' => ['ndla'];
    }

    #[DataProvider('provider_setPreview')]
    public function test_setPreview(bool $preview, string $contentUserData, string $setFinished): void
    {
        $data = app(H5PViewConfig::class)
            ->setContext('something')
            ->setPreview($preview)
            ->getConfig();

        $this->assertSame($contentUserData, $data->ajax['contentUserData']);
        $this->assertSame($setFinished, $data->ajax['setFinished']);
    }

    public static function provider_setPreview(): Generator
    {
        yield [
            false,
            '/api/progress?action=h5p_contents_user_data&content_id=:contentId&data_type=:dataType&sub_content_id=:subContentId&context=something',
            '/api/progress?action=h5p_setFinished',
        ];
        yield [
            true,
            '/api/progress?action=h5p_preview&c=1',
            '/api/progress?action=h5p_preview&f=1',
        ];
    }

    #[DataProvider('provider_adapterMode')]
    public function test_loadContent(string $adapterMode): void
    {
        Session::put('adapterMode', $adapterMode);

        $context = $this->faker->uuid;
        $library = H5PLibrary::factory()->create();
        $dependency = H5PLibrary::factory()->create(['name' => 'FontOk']);
        H5PLibraryLibrary::create([
            'library_id' => $library->id,
            'required_library_id' => $dependency->id,
            'dependency_type' => 'preloaded',
        ]);
        $content = H5PContent::factory()->create([
            'library_id' => $library->id,
            'disable' => 8,
            'parameters' =>  '{"title":"something else"}',
        ]);
        H5PContentsMetadata::factory()->create([
            'content_id' => $content->id,
            'authors' => '[{"name":"Emily Quackfaster","role":"Author"}]',
            'license' => 'CC BY-NC-ND',
            'license_version' => '4.0',
            'default_language' => 'nb',
        ]);

        $data = app(H5PViewConfig::class)
            ->setUserId($this->faker->uuid)
            ->setContext($context)
            ->setEmbedId('my-embed-id')
            ->loadContent($content->id)
            ->getConfig();

        $this->assertDatabaseHas('h5p_contents_libraries', ['content_id' => 1, 'library_id' => $library->id, 'dependency_type' => 'preloaded']);
        $this->assertDatabaseHas('h5p_contents_libraries', ['content_id' => 1, 'library_id' => $dependency->id, 'dependency_type' => 'preloaded']);

        $this->assertTrue($data->postUserStatistics);
        $this->assertObjectHasProperty('cid-' . $content->id, $data->contents);
        $this->assertSame(
            "/api/progress?action=h5p_contents_user_data&content_id=:contentId&data_type=:dataType&sub_content_id=:subContentId&context=$context",
            $data->ajax['contentUserData'],
        );
        $this->assertSame("https://www.edlib.test/s/resources/my-embed-id", $data->documentUrl);

        $contentData = $data->contents->{'cid-' . $content->id};

        $this->assertSame('H5P.Foobar 1.2', $contentData->library);
        $this->assertSame(1, $contentData->fullScreen);
        $this->assertStringEndsWith("/h5p/$content->id/download", $contentData->exportUrl);
        $this->assertStringContainsString("/s/resources/my-embed-id", $contentData->embedCode);
        $this->assertNotEmpty($data->url);
        $this->assertSame($content->title, $contentData->title);
        $this->assertSame(config('h5p.saveFrequency'), $data->saveFreq);

        if (config('h5p.saveFrequency') === false) {
            $this->assertObjectNotHasProperty('user', $data);
        } else {
            $this->assertObjectHasProperty('user', $data);
        }

        $this->assertSame('Emily Quackfaster', $contentData->metadata['authors'][0]->name);
        $this->assertSame('CC BY-NC-ND', $contentData->metadata['license']);
        $this->assertSame('4.0', $contentData->metadata['licenseVersion']);
        $this->assertSame('nb', $contentData->metadata['defaultLanguage']);

        $this->assertTrue($contentData->displayOptions->frame);
        $this->assertTrue($contentData->displayOptions->export);
        $this->assertTrue($contentData->displayOptions->embed);
        $this->assertFalse($contentData->displayOptions->copyright);
        $this->assertTrue($contentData->displayOptions->icon);
        $this->assertNull($contentData->displayOptions->copy);

        $this->assertFalse($contentData->contentUserData['state']);
    }

    public function test_setAlterParameterSettings(): void
    {
        $library = H5PLibrary::factory()->create();
        $content = H5PContent::factory()->create([
            'library_id' => $library->id,
            'disable' => 2,
            'filtered' => '{"here be":"filtered data"}',
        ]);

        $data = app(H5PViewConfig::class)
            ->loadContent($content->id)
            ->setAlterParameterSettings(new H5PAlterParametersSettingsDataObject(useImageWidth: $content->library->includeImageWidth()))
            ->getConfig();

        $this->assertSame($content->filtered, $data->contents->{"cid-$content->id"}->jsonContent);
    }

    public function test_behaviorSettings(): void
    {
        $library = H5PLibrary::factory()->create();
        $content = H5PContent::factory()->create([
            'library_id' => $library->id,
            'disable' => 8,
            'filtered' => '{"title":"something"}',
        ]);

        Session::put(SessionKeys::EXT_BEHAVIOR_SETTINGS, BehaviorSettingsDataObject::create([
            'presetmode' => 'exam',
        ]));

        $data = app(H5PViewConfig::class)
            ->loadContent($content->id)
            ->setAlterParameterSettings(new H5PAlterParametersSettingsDataObject(useImageWidth: $content->library->includeImageWidth()))
            ->getConfig();

        $this->assertSame($content->filtered, $data->contents->{"cid-$content->id"}->jsonContent);
    }
}
