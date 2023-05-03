<?php

use App\H5PLibrary;
use Tests\TestCase;

class H5PLibraryTest extends TestCase
{
    /**
     * @dataProvider provider_LibraryString
     */
    public function test_getLibraryString($isFolder, $usePatch, $expected): void
    {
        /** @var H5PLibrary $lib */
        $lib = H5PLibrary::factory()->make([
            'patch_version_in_folder_name' => $usePatch,
        ]);

        $this->assertEquals($expected, $lib->getLibraryString($isFolder));
    }

    public function provider_LibraryString(): Generator
    {
        yield [true, false, 'H5P.Foobar-1.2'];
        yield [true, true, 'H5P.Foobar-1.2.3'];
        yield [false, false, 'H5P.Foobar 1.2'];
        yield [false, true, 'H5P.Foobar 1.2'];
    }

    /**
     * @dataProvider provider_libraryToString
     */
    public function test_libraryToString($usePatch, $expected): void
    {
        /** @var H5PLibrary $lib */
        $lib = H5PLibrary::factory()->make([
            'patch_version_in_folder_name' => $usePatch,
        ]);
        $this->assertEquals($expected, $lib->libraryToString());
    }

    public function provider_libraryToString(): Generator
    {
        yield [false, 'H5P.Foobar 1.2'];
        yield [true, 'H5P.Foobar 1.2.3'];
    }
}
