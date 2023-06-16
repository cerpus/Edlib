<?php

namespace Tests\Integration\Libraries\H5P;

use App\ApiModels\Resource;
use App\Apis\ResourceApiService;
use App\Libraries\DataObjects\BehaviorSettingsDataObject;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\ViewConfig;
use App\SessionKeys;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewConfigTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpFaker();
    }

    public function test_getEdlibUrl(): void
    {
        $adapter = $this->createMock(H5PAdapterInterface::class);
        $core = $this->createMock(\H5PCore::class);
        $resourceApi = $this->createMock(ResourceApiService::class);
        $viewConfig = new ViewConfig($adapter, $core, $resourceApi);

        $id = $this->faker->uuid;
        $resourceApi
            ->expects($this->once())
            ->method('getResourceFromExternalReference')
            ->willReturn(new Resource($id, '', '' ,'', '', ''));

        $viewConfig = $viewConfig->setId($id);

        $url = $viewConfig->getEdlibUrl();
        $this->assertStringEndsWith("/s/resources/$id", $url);
    }

    public function test_getConfig(): void
    {
        $resourceId = $this->faker->uuid;
        $content = [
            'id' => 42,
            'contentId' => 42,
            'params' => $this->faker->sentence,
            'filtered' => $this->faker->sentence,
            'embedType' => '',
            'title' => $this->faker->sentence,
            'disable' => false,
            'user_id' => 1,
            'slug' => 'test',
            'libraryId' => 1,
            'libraryName' => 'H5P.Test',
            'libraryMajorVersion' => 1,
            'libraryMinorVersion' => 3,
            'libraryEmbedTypes' => 5,
            'libraryFullscreen' => false,
            'language' => null,
            'max_score' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'metadata' => [],
            'library' => [
                'name' => 'H5P.Test',
                'majorVersion' => 1,
                'minorVersion' => 3,
                'fullscreen' => false,
            ],
        ];

        $resourceApi = $this->createMock(ResourceApiService::class);
        $resourceApi
            ->expects($this->once())
            ->method('getResourceFromExternalReference')
            ->willReturn(new Resource($resourceId, '', '', '', '', '', $content['title']));

        $viewConfig = new ViewConfig(
            app(H5PAdapterInterface::class),
            app(\H5PCore::class),
            $resourceApi
        );
        $viewConfig->config = new \stdClass();

        $viewConfig->setId($resourceId);
        $viewConfig->content = $content;

        $behaviourObject = BehaviorSettingsDataObject::create([
            'includeAnswers' => false,
        ]);
        session()->put(SessionKeys::EXT_BEHAVIOR_SETTINGS, $behaviourObject);

        $config = $viewConfig->getConfig();

        $this->assertStringEndsWith("/s/resources/$resourceId", $config->documentUrl);
        $contents = $config->contents->{'cid-42'};
        $this->assertEquals('H5P.Test 1.3', $contents->library);
        $this->assertEquals($content['title'], $contents->title);
        $this->assertStringEndsWith('/h5p/42/download', $contents->exportUrl);
        $this->assertStringContainsStringIgnoringCase($config->documentUrl, $contents->embedCode);
        $this->assertStringContainsString($content['title'], $contents->embedCode);
        $this->assertObjectHasAttribute('resizeCode', $contents);
        $this->assertObjectHasAttribute('displayOptions', $contents);
        $this->assertObjectHasAttribute('contentUserData', $contents);
    }
}
