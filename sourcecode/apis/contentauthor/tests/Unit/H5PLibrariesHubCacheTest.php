<?php

namespace Tests\Unit;

use App\H5PLibrariesHubCache;
use Tests\TestCase;

class H5PLibrariesHubCacheTest extends TestCase
{
    /**
     * @dataProvider provider_LibraryString
     */
    public function test_getLibraryString($isFolder, $usePatch, $expected): void
    {
        $lib = new H5PLibrariesHubCache([
            'name' => 'H5P.Foobar',
            'major_version' => 1,
            'minor_version' => 2,
            'patch_version_in_folder_name' => $usePatch,
        ]);

        $this->assertSame($expected, $lib->getLibraryString($isFolder));
    }

    public function provider_LibraryString(): \Generator
    {
        yield [true, false, 'H5P.Foobar-1.2'];
        yield [true, true, 'H5P.Foobar-1.2'];
        yield [false, false, 'H5P.Foobar 1.2'];
        yield [false, true, 'H5P.Foobar 1.2'];
    }
}
