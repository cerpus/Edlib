<?php

namespace Tests\Integration\Libraries\H5P;

use App\ApiModels\Resource;
use App\Apis\ResourceApiService;
use App\H5PContent;
use App\H5PLibrary;
use App\Libraries\H5P\Storage\H5PCerpusStorage;
use App\Libraries\H5P\ViewConfig;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewConfigTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

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

        $storage = $this->createMock(H5PCerpusStorage::class);
        $this->instance(H5PCerpusStorage::class, $storage);
        $storage->expects($this->once())->method('getDisplayPath')->with(false)->willReturn('/yupp');

        $resourceApi = $this->createMock(ResourceApiService::class);
        $this->instance(ResourceApiService::class, $resourceApi);
        $resourceApi
            ->expects($this->exactly(2))
            ->method('getResourceFromExternalReference')
            ->with('contentauthor', $h5pcontent->id)
            ->willReturn(
                new Resource(
                    $this->faker->uuid,
                    $this->faker->uuid,
                    '',
                    null,
                    '',
                    '',
                    'Test resource'
                )
            );

        $core = app(\H5PCore::class);
        $content = $core->loadContent($h5pcontent->id);

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
        $this->assertSame($library->getLibraryString(), $viewContents->library);
        $this->assertSame($h5pcontent->title, $viewContents->title);
    }

    public function provider_testConfig(): \Generator
    {
        yield [false];
        yield [true];
    }
}
