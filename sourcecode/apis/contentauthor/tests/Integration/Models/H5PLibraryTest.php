<?php

namespace Tests\Integration\Models;

use App\H5PLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class H5PLibraryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider provider_getLibraryString
     */
    public function test_getLibraryString($usePatch, $hasPatch, $expected): void
    {
        $lib = H5PLibrary::factory()->make([
            'patch_version_in_folder_name' => $hasPatch,
        ]);

        $this->assertSame($expected, $lib->getLibraryString($usePatch));
    }

    public function provider_getLibraryString(): \Generator
    {
        yield 0 => [null, false, 'H5P.Foobar 1.2'];
        yield 1 => [null, true, 'H5P.Foobar 1.2.3'];
        yield 2 => [true, false, 'H5P.Foobar 1.2.3'];
        yield 3 => [true, true, 'H5P.Foobar 1.2.3'];
        yield 4 => [false, false, 'H5P.Foobar 1.2'];
        yield 5 => [false, true, 'H5P.Foobar 1.2'];
    }

    /**
     * @dataProvider provider_getFolderName
     */
    public function test_getFolderName($usePatch, $hasPatch, $expected): void
    {
        $lib = H5PLibrary::factory()->make([
            'patch_version_in_folder_name' => $hasPatch,
        ]);

        $this->assertSame($expected, $lib->getFolderName($usePatch));
    }

    public function provider_getFolderName(): \Generator
    {
        yield 0 => [null, false, 'H5P.Foobar-1.2'];
        yield 1 => [null, true, 'H5P.Foobar-1.2.3'];
        yield 2 => [true, false, 'H5P.Foobar-1.2.3'];
        yield 3 => [true, true, 'H5P.Foobar-1.2.3'];
        yield 4 => [false, false, 'H5P.Foobar-1.2'];
        yield 5 => [false, true, 'H5P.Foobar-1.2'];
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
        yield 2 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersion' => 4, 'patchVersionInFolderName' => true], false, 'H5P.Foobar 2.1'];
        yield 3 => [['name' => 'H5P.Foobar', 'majorVersion' => 2, 'minorVersion' => 1, 'patchVersion' => 4, 'patchVersionInFolderName' => false], true, 'H5P.Foobar 2.1.4'];
    }

    public function test_getUpgrades()
    {
        $originalLib = H5PLibrary::factory()->create();
        $noPatchLibrary = H5PLibrary::factory()->create([
            'minor_version' => 3,
        ]);
        $withPatchLibrary = H5PLibrary::factory()->create([
            'minor_version' => 4,
            'patch_version_in_folder_name' => true,
        ]);

        /** @var Collection $availableUpdates */
        $availableUpdates = $originalLib->getUpgrades(false);

        $this->assertCount(2, $availableUpdates);

        $noPatch = $availableUpdates->where('id', $noPatchLibrary->id)->first();
        $this->assertSame('H5P.Foobar 1.3', $noPatch['name']);
        $this->assertSame('1.3.3', $noPatch['version']);

        $withPatch = $availableUpdates->where('id', $withPatchLibrary->id)->first();
        $this->assertSame('H5P.Foobar 1.4', $withPatch['name']);
        $this->assertSame('1.4.3', $withPatch['version']);
    }
}
