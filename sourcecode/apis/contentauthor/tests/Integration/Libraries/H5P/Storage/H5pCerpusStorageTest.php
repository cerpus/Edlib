<?php

namespace Tests\Integration\Libraries\H5P\Storage;

use App\H5PLibrary;
use App\Libraries\ContentAuthorStorage;
use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\H5P\Storage\H5PCerpusStorage;
use App\Libraries\H5P\Video\NullVideoAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Psr\Log\NullLogger;
use Tests\TestCase;

class H5pCerpusStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_correct_url_without_cdn_prefix()
    {
        Storage::fake('test');
        Storage::put('test.txt', 'some content');

        $cerpusStorage = new H5pCerpusStorage(
            new ContentAuthorStorage(''),
            new NullLogger(),
            new NullVideoAdapter(),
        );
        $this->assertEquals("http://localhost/content/assets/test.txt", $cerpusStorage->getFileUrl('test.txt'));
        Storage::delete('test.txt');
    }

    public function test_correct_url_with_cdn_prefix()
    {
        Storage::fake('test');
        Storage::put('test.txt', 'some content');

        $cerpusStorage = new H5pCerpusStorage(
            new ContentAuthorStorage('http://not.localhost/prefix/'),
            new NullLogger(),
            new NullVideoAdapter(),
        );

        $this->assertEquals("http://not.localhost/prefix/test.txt", $cerpusStorage->getFileUrl('test.txt'));
        Storage::delete('test.txt');
    }

    public function test_correct_url_when_file_not_found()
    {
        Storage::fake('test');
        $cerpusStorage = new H5pCerpusStorage(
            new ContentAuthorStorage(''),
            new NullLogger(),
            new NullVideoAdapter(),
        );

        $this->assertEquals("", $cerpusStorage->getFileUrl('test.txt'));
    }

    /** @dataProvider provider_getUpdateScript */
    public function test_getUpgradeScript($data): void
    {
        H5PLibrary::factory()->create(array_merge($data, ['major_version' => $data['major_version'] - 1]));
        H5PLibrary::factory()->create(array_merge($data, ['minor_version' => $data['minor_version'] - 1]));

        /** @var H5PLibrary $library */
        $library = H5PLibrary::factory()->create($data);

        H5PLibrary::factory()->create(array_merge($data, ['minor_version' => $data['minor_version'] + 1]));
        H5PLibrary::factory()->create(array_merge($data, ['major_version' => $data['major_version'] + 1]));

        $folder = $library->getLibraryString(true);
        $expected = sprintf(ContentStorageSettings::UPGRADE_SCRIPT_PATH, $folder);

        Storage::fake('test');
        Storage::put($expected, 'Empty');

        $cerpusStorage = new H5pCerpusStorage(
            new ContentAuthorStorage(''),
            new NullLogger(),
            new NullVideoAdapter(),
        );

        $path = $cerpusStorage->getUpgradeScript($data['name'], $data['major_version'], $data['minor_version']);
        $this->assertSame("/$expected", $path);
        Storage::delete($expected);
    }

    public function provider_getUpdateScript(): \Generator
    {
        yield [[
            'name' => 'H5P.Foobar',
            'major_version' => 2,
            'minor_version' => 3,
            'patch_version' => 4,
        ]];
        yield [[
            'name' => 'H5P.Foobar',
            'major_version' => 2,
            'minor_version' => 3,
            'patch_version' => 4,
            'patch_version_in_folder_name' => true,
        ]];
    }

    /** @dataProvider provider_deleteLibrary */
    public function test_deleteLibrary($data): void
    {
        $path = sprintf(ContentStorageSettings::LIBRARY_PATH, H5PLibrary::libraryToFolderName($data));

        Storage::fake('test');
        Storage::createDirectory($path);

        $cerpusStorage = new H5pCerpusStorage(
            new ContentAuthorStorage(''),
            new NullLogger(),
            new NullVideoAdapter(),
        );

        $this->assertTrue(Storage::exists($path));
        $ret = $cerpusStorage->deleteLibrary($data);
        $this->assertTrue($ret);
        $this->assertFalse(Storage::exists($path));
    }

    public function provider_deleteLibrary(): \Generator
    {
        yield [[
           'name' => 'H5P.Foobar',
           'majorVersion' => 2,
           'minorVersion' => 3,
           'patchVersion' => 4,
        ]];
        yield [[
           'name' => 'H5P.Foobar',
           'majorVersion' => 2,
           'minorVersion' => 3,
           'patchVersion' => 4,
           'patchVersionInFolderName' => true,
        ]];
    }
}
