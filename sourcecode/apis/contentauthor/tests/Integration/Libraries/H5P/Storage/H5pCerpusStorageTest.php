<?php

namespace Tests\Integration\Libraries\H5P\Storage;

use App\H5PLibrary;
use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\H5P\Storage\H5PCerpusStorage;
use App\Libraries\H5P\Video\NullVideoAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\NullLogger;
use Tests\TestCase;

class H5pCerpusStorageTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('provide_test_getUpdateScript')]
    public function test_getUpgradeScript(array $libConfig): void
    {
        $disk = Storage::fake();

        $library = H5PLibrary::factory()->create($libConfig);
        $file = sprintf(ContentStorageSettings::UPGRADE_SCRIPT_PATH, $library->getFolderName());

        $this->assertFalse($disk->exists($file));
        $disk->put($file, 'just testing');
        $this->assertTrue($disk->exists($file));

        $cerpusStorage = new H5pCerpusStorage(
            new NullLogger(),
            new NullVideoAdapter(),
        );

        $path = $cerpusStorage->getUpgradeScript($library->getFolderName(), $library->major_version, $library->minor_version);

        $this->assertStringContainsString($file, $path);
    }

    public function test_cacheAssets(): void
    {
        $disk = Storage::fake();
        Storage::fake('h5pTmp');
        $disk->put('libraries/H5P.Blanks-1.14.6/js/blanks.js', 'var blanks;');
        $disk->put('libraries/H5P.Blanks-1.14.6/css/blanks.css', 'body{background:url(../img/bg.png)}');

        $files = [
            'scripts' => [
                (object) ['path' => 'libraries/H5P.Blanks-1.14.6/js/blanks.js', 'version' => '?ver=1.14.6'],
            ],
            'styles' => [
                (object) ['path' => 'libraries/H5P.Blanks-1.14.6/css/blanks.css', 'version' => '?ver=1.14.6'],
            ],
        ];

        $cerpusStorage = new H5PCerpusStorage(
            new NullLogger(),
            new NullVideoAdapter(),
        );

        $cerpusStorage->cacheAssets($files, 'somehash');

        $this->assertSame('cachedassets/somehash.js', $files['scripts'][0]->path);
        $this->assertSame('cachedassets/somehash.css', $files['styles'][0]->path);
        $this->assertStringContainsString('var blanks;', $disk->get('cachedassets/somehash.js'));
        $this->assertStringContainsString(
            'url("../libraries/H5P.Blanks-1.14.6/css/../img/bg.png")',
            $disk->get('cachedassets/somehash.css'),
        );
    }

    public function test_cacheAssetsSkipsIncompleteBundles(): void
    {
        $disk = Storage::fake();
        Storage::fake('h5pTmp');
        $disk->put('libraries/H5P.Blanks-1.14.6/js/blanks.js', 'var blanks;');

        $files = [
            'scripts' => [
                (object) ['path' => 'libraries/H5P.Blanks-1.14.6/js/blanks.js', 'version' => '?ver=1.14.6'],
                (object) ['path' => 'libraries/H5P.CoursePresentation-1.27.17/dist/cp.js', 'version' => '?ver=1.27.17'],
            ],
            'styles' => [],
        ];

        $cerpusStorage = new H5PCerpusStorage(
            new NullLogger(),
            new NullVideoAdapter(),
        );

        $cerpusStorage->cacheAssets($files, 'somehash');

        // Aggregating only some of the files would break every library in the
        // bundle, so nothing must be written and the files must be left alone.
        $this->assertFalse($disk->exists('cachedassets/somehash.js'));
        $this->assertCount(2, $files['scripts']);
        $this->assertSame('libraries/H5P.Blanks-1.14.6/js/blanks.js', $files['scripts'][0]->path);
    }

    public function test_cacheAssetsUsesFreshlyUploadedLibraryFiles(): void
    {
        $disk = Storage::fake();
        $uploadDisk = Storage::fake('h5pTmp');
        $disk->put('libraries/H5P.Blanks-1.14.6/js/blanks.js', 'var stale;');
        $uploadDisk->put('libraries/H5P.Blanks-1.14.6/library.json', json_encode([
            'majorVersion' => 1,
            'minorVersion' => 14,
            'patchVersion' => 6,
        ]));
        $uploadDisk->put('libraries/H5P.Blanks-1.14.6/js/blanks.js', 'var uploaded;');

        $files = [
            'scripts' => [
                (object) ['path' => 'libraries/H5P.Blanks-1.14.6/js/blanks.js', 'version' => '?ver=1.14.6'],
            ],
            'styles' => [],
        ];

        $cerpusStorage = new H5PCerpusStorage(
            new NullLogger(),
            new NullVideoAdapter(),
        );

        $cerpusStorage->cacheAssets($files, 'somehash');

        $this->assertStringContainsString('var uploaded;', $disk->get('cachedassets/somehash.js'));
    }

    public static function provide_test_getUpdateScript(): \Generator
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
