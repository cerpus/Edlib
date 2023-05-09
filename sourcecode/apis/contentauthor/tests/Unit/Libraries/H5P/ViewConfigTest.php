<?php

namespace Libraries\H5P;

use App\ApiModels\Resource;
use App\Apis\ResourceApiService;
use App\H5PContent;
use App\H5PLibrary;
use App\Libraries\H5P\Storage\H5PCerpusStorage;
use App\Libraries\H5P\ViewConfig;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewConfigTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider provider_testConfig
     */
    public function testConfig($usePatch): void
    {
        $user = User::factory()->make();
        /** @var H5PLibrary $library */
        $library = H5PLibrary::factory()->create(['patch_version_in_folder_name' => $usePatch]);
        /** @var H5PContent $h5pcontent */
        $h5pcontent = H5PContent::factory()->create(['user_id' => $user->auth_id, 'library_id' => $library->id]);

        $core = $this->createMock(\H5PCore::class);
        $this->instance(\H5PCore::class, $core);
        $core->expects($this->once())->method('getLocalization')->willReturn([]);
        $core->expects($this->exactly(2))->method('loadContentDependencies')->willReturn([]);
        $core->expects($this->once())->method('getDependenciesFiles')->willReturn([]);

        $storage = $this->createMock(H5PCerpusStorage::class);
        $this->instance(H5PCerpusStorage::class, $storage);
        $core->fs = $storage;
        $storage->expects($this->once())->method('getDisplayPath')->with(false)->willReturn('/yupp');

        $resourceApi = $this->createMock(ResourceApiService::class);
        $this->instance(ResourceApiService::class, $resourceApi);
        $resourceApi
            ->expects($this->exactly(2))
            ->method('getResourceFromExternalReference')
            ->with('contentauthor', $h5pcontent->id)
            ->willReturn(
                new Resource(
                    42,
                    24,
                    '',
                    null,
                    '',
                    '',
                    'Test resource'
                )
            );

        $content = [
            'id' => $h5pcontent->id,
            'contentId' => $h5pcontent->id,
            'params' => $h5pcontent->parameters,
            'filtered' => $h5pcontent->filtered,
            'embedType' => $h5pcontent->embed_type,
            'title' => $h5pcontent->title,
            'disable' => $h5pcontent->disable,
            'user_id' => $h5pcontent->user_id,
            'slug' => $h5pcontent->slug,
            'libraryId' => $h5pcontent->library->id,
            'libraryName' => $h5pcontent->library->name,
            'libraryMajorVersion' => $h5pcontent->library->major_version,
            'libraryMinorVersion' => $h5pcontent->library->minor_version,
            'libraryPatchVersion' => $h5pcontent->library->patch_version,
            'libraryFullVersionName' => $h5pcontent->library->getLibraryString(false, false),
            'libraryEmbedTypes' => $h5pcontent->library->embed_types,
            'libraryFullscreen' => $h5pcontent->library->fullscreen,
            'language' => $h5pcontent->metadata->default_language ?? null,
            'max_score' => $h5pcontent->max_score,
            'created_at' => $h5pcontent->created_at,
            'updated_at' => $h5pcontent->updated_at,
            'library' => [
                'id' => $library->id,
                'name' => $library->name,
                'fullscreen' => $library->fullscreen,
            ],
            'metadata' => [],
        ];

        $viewConfig = app(ViewConfig::class)
            ->setId($h5pcontent->id)
            ->setUserId($user->auth_id)
            ->setUserName($user->name)
            ->setEmail($user->email)
            ->setPreview(false)
            ->setContext('nope');

        $viewConfig->setContent($content);

        $config = $viewConfig->getConfig();

        $this->assertSame('/yupp', $config->url);
        $this->assertSame($user->email, $config->user->mail);
        $viewContents = $config->contents->{'cid-' . $h5pcontent->id};
        $this->assertSame('H5P.Foobar 1.2', $viewContents->library);
        $this->assertSame($h5pcontent->title, $viewContents->title);
    }

    public function provider_testConfig(): \Generator
    {
        yield [false];
        yield [true];
    }
}
