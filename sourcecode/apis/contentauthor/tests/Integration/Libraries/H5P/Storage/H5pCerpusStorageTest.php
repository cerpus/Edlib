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
        $disk = Storage::fake('test');
        $disk->put('test.txt', 'some content');

        $cerpusStorage = new H5pCerpusStorage(
            new ContentAuthorStorage(''),
            new NullLogger(),
            new NullVideoAdapter(),
        );

        $this->assertEquals("http://localhost/content/assets/test.txt", $cerpusStorage->getFileUrl('test.txt'));
    }

    public function test_correct_url_with_cdn_prefix()
    {
        $disk = Storage::fake('test');
        $disk->put('test.txt', 'some content');

        $cerpusStorage = new H5pCerpusStorage(
            new ContentAuthorStorage('https://not.localhost.test/prefix/'),
            new NullLogger(),
            new NullVideoAdapter(),
        );

        $this->assertEquals("https://not.localhost.test/prefix/test.txt", $cerpusStorage->getFileUrl('test.txt'));
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

    /** @dataProvider provide_test_getUpdateScript */
    public function test_getUpgradeScript(array $libConfig): void
    {
        $disk = Storage::fake();

        $library = H5PLibrary::factory()->create($libConfig);
        $file = sprintf(ContentStorageSettings::UPGRADE_SCRIPT_PATH, $library->getFolderName());

        $this->assertFalse($disk->exists($file));
        $disk->put($file, 'just testing');
        $this->assertTrue($disk->exists($file));

        $cerpusStorage = new H5pCerpusStorage(
            new ContentAuthorStorage(''),
            new NullLogger(),
            new NullVideoAdapter(),
        );

        $path = $cerpusStorage->getUpgradeScript($library->name, $library->major_version, $library->minor_version);

        $this->assertStringContainsString($file, $path);
    }

    public function provide_test_getUpdateScript(): \Generator
    {
        yield 'withoutPatch' => [[
           'name' => 'H5P.Blanks',
           'major_version' => 1,
           'minor_version' => 11,
       ]];

        yield 'withPatch' => [[
           'name' => 'H5P.Blanks',
           'major_version' => 1,
           'minor_version' => 14,
           'patch_version' => 6,
           'patch_version_in_folder_name' => 1,
       ]];
    }
}
