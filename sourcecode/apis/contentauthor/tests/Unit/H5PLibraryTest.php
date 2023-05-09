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
        yield 'Folder 1' => [true, null, false, 'H5P.Foobar-1.2'];
        yield 'Folder 2' => [true, null, true, 'H5P.Foobar-1.2.3'];
        yield 'Folder 3' => [true, true, false, 'H5P.Foobar-1.2.3'];
        yield 'Folder 4' => [true, true, true, 'H5P.Foobar-1.2.3'];
        yield 'Folder 5' => [true, false, false, 'H5P.Foobar-1.2'];
        yield 'Folder 6' => [true, false, true, 'H5P.Foobar-1.2'];

        yield 'Name 1' => [false, null, false, 'H5P.Foobar 1.2'];
        yield 'Name 2' => [false, null, true, 'H5P.Foobar 1.2.3'];
        yield 'Name 3' => [false, true, false, 'H5P.Foobar 1.2.3'];
        yield 'Name 4' => [false, true, true, 'H5P.Foobar 1.2.3'];
        yield 'Name 5' => [false, false, false, 'H5P.Foobar 1.2'];
        yield 'Name 6' => [false, false, true, 'H5P.Foobar 1.2'];
    }

    /** @dataProvider provider_libraryToFolderName */
    public function test_libraryToFolderName($data, $usePatch, $expected): void
    {
        $this->assertSame($expected, H5PLibrary::libraryToFolderName($data, $usePatch));
    }

    public function provider_libraryToFolderName(): \Generator
    {
        yield 0 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersion' => 4, 'patchVersionInFolderName' => true], false, 'H5P.Foobar-2.1'];
        yield 1 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersion' => 4, 'patchVersionInFolderName' => false], true, 'H5P.Foobar-2.1.4'];
        yield 2 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersion' => 4, 'patchVersionInFolderName' => 0], null, 'H5P.Foobar-2.1'];
        yield 3 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersion' => 4, 'patchVersionInFolderName' => 1], null, 'H5P.Foobar-2.1.4'];
        yield 4 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersion' => 4], true, 'H5P.Foobar-2.1.4'];
        yield 5 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersion' => 4], null, 'H5P.Foobar-2.1'];
        yield 6 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersionInFolderName' => true], false, 'H5P.Foobar-2.1'];
    }

    /** @dataProvider provider_libraryToFolderNameExceptions */
    public function test_libraryToFolderNameExceptions($data, $usePatch): void
    {
        $this->expectException(\InvalidArgumentException::class);
        H5PLibrary::libraryToFolderName($data, $usePatch);
    }

    public function provider_libraryToFolderNameExceptions(): \Generator
    {
        yield 0 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersionInFolderName' => true], true];
        yield 1 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersionInFolderName' => false], true];
        yield 2 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersionInFolderName' => true], null];
    }

    /** @dataProvider provider_libraryToString */
    public function test_libraryToString($data, $usePatch, $expected): void
    {
        $this->assertSame($expected, H5PLibrary::libraryToString($data, $usePatch));
    }

    public function provider_libraryToString(): \Generator
    {
        yield 0 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersion' => 4, 'patchVersionInFolderName' => true], null, 'H5P.Foobar 2.1.4'];
        yield 1 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersion' => 4, 'patchVersionInFolderName' => false], null, 'H5P.Foobar 2.1'];
        yield 1 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersion' => 4, 'patchVersionInFolderName' => true], false, 'H5P.Foobar 2.1'];
        yield 1 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersion' => 4, 'patchVersionInFolderName' => false], true, 'H5P.Foobar 2.1.4'];
    }
}
