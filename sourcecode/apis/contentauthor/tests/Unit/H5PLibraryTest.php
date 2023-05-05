<?php

namespace Tests\Unit;

use App\H5PLibrary;
use Tests\TestCase;

class H5PLibraryTest extends TestCase
{
    /**
     * @dataProvider provider_libraryString
     */
    public function test_getLibraryString($isFolder, $usePatch, $hasPatch, $expected): void
    {
        /** @var H5PLibrary $lib */
        $lib = H5PLibrary::factory()->make([
            'patch_version_in_folder_name' => $hasPatch,
        ]);

        $this->assertSame($expected, $lib->getLibraryString($isFolder, $usePatch));
    }

    public function provider_libraryString(): \Generator
    {
        yield [true, null, false, 'H5P.Foobar-1.2'];
        yield [true, null, true, 'H5P.Foobar-1.2.3'];
        yield [true, true, false, 'H5P.Foobar-1.2.3'];
        yield [true, true, true, 'H5P.Foobar-1.2.3'];
        yield [true, false, false, 'H5P.Foobar-1.2'];
        yield [true, false, true, 'H5P.Foobar-1.2'];

        yield [false, null, false, 'H5P.Foobar 1.2'];
        yield [false, null, true, 'H5P.Foobar 1.2.3'];
        yield [false, true, false, 'H5P.Foobar 1.2.3'];
        yield [false, true, true, 'H5P.Foobar 1.2.3'];
        yield [false, false, false, 'H5P.Foobar 1.2'];
        yield [false, false, true, 'H5P.Foobar 1.2'];
    }
}
