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

    /** @dataProvider provider_usePatch */
    public function test_getUpgradeScript($usePatch): void
    {
        H5PLibrary::factory()->create([
            'major_version' => 1,
            'minor_version' => 3,
            'patch_version' => 4,
            'patch_version_in_folder_name' => $usePatch
        ]);
        H5PLibrary::factory()->create([
            'major_version' => 2,
            'minor_version' => 2,
            'patch_version' => 4,
            'patch_version_in_folder_name' => $usePatch
        ]);

        /** @var H5PLibrary $library */
        $library = H5PLibrary::factory()->create(['patch_version_in_folder_name' => $usePatch]);

        H5PLibrary::factory()->create([
            'major_version' => 2,
            'minor_version' => 4,
            'patch_version' => 4,
            'patch_version_in_folder_name' => $usePatch
        ]);
        H5PLibrary::factory()->create([
            'major_version' => 3,
            'minor_version' => 3,
            'patch_version' => 4,
            'patch_version_in_folder_name' => $usePatch
        ]);

        $folder = $library->getLibraryString(true);
        $expected = sprintf(ContentStorageSettings::UPGRADE_SCRIPT_PATH, $folder);

        Storage::fake('test');
        Storage::put($expected, 'Empty');

        $cerpusStorage = new H5pCerpusStorage(
            new ContentAuthorStorage(''),
            new NullLogger(),
            new NullVideoAdapter(),
        );

        $path = $cerpusStorage->getUpgradeScript($library->name, $library->major_version, $library->minor_version);
        $this->assertSame("/$expected", $path);
        Storage::delete($expected);
    }

    public function provider_usePatch(): \Generator
    {
        yield [false];
        yield [true];
    }

    /** @dataProvider provider_usePatch */
    public function test_deleteLibrary($usePatch): void
    {
        $data = [
            'name' => 'H5P.Foobar',
            'majorVersion' => 2,
            'minorVersion' => 3,
            'patchVersion' => 4,
            'patchVersionInFolderName' => $usePatch,
        ];
        $path = sprintf(ContentStorageSettings::LIBRARY_PATH, H5PLibrary::libraryToFolderName($data));

        Storage::fake('test');
        Storage::createDirectory($path);

        $cerpusStorage = new H5pCerpusStorage(
            new ContentAuthorStorage(''),
            new NullLogger(),
            new NullVideoAdapter(),
        );

        $this->assertTrue(Storage::exists($path));
        $this->assertTrue($cerpusStorage->deleteLibrary($data));
        $this->assertFalse(Storage::exists($path));
    }
}
