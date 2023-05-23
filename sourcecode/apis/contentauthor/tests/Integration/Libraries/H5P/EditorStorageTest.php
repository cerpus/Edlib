<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PLibrary;
use App\Libraries\H5P\EditorStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_getLibraries_forLibrary(): void
    {
        /** @var H5PLibrary $library */
        $library = H5PLibrary::factory()->create(['semantics' => 'something']);
        $libraries = (object) [
            'uberName' => $library->getLibraryString(false),
            'name' => $library->name,
            'majorVersion' => $library->major_version,
            'minorVersion' => $library->minor_version,
        ];

        $core = $this->createMock(\H5PCore::class);
        $this->instance(\H5PCore::class, $core);

        /** @var EditorStorage $editorStorage */
        $editorStorage = app(EditorStorage::class);
        $ret = $editorStorage->getLibraries([$libraries]);

        $this->assertCount(1, $ret);
        $this->assertSame($library->id, $ret[0]->id);
        $this->assertSame('H5P.Foobar 1.2', $ret[0]->uberName);
    }

    public function test_getLibrary_allLibraries(): void
    {
        /** @var H5PLibrary $lib1 */
        $lib1 = H5PLibrary::factory()->create(['semantics' => 'something']);
        /** @var H5PLibrary $lib2 */
        $lib2 = H5PLibrary::factory()->create(['name' => 'H5P.Headphones', 'semantics' => 'something']);

        $core = $this->createMock(\H5PCore::class);
        $this->instance(\H5PCore::class, $core);

        /** @var EditorStorage $editorStorage */
        $editorStorage = app(EditorStorage::class);
        $ret = $editorStorage->getLibraries();

        $this->assertCount(2, $ret);
        $this->assertSame($lib1->id, $ret[0]->id);
        $this->assertSame($lib2->id, $ret[1]->id);

        $this->assertSame('H5P.Foobar 1.2', $ret[0]->uberName);
        $this->assertSame('H5P.Headphones 1.2', $ret[1]->uberName);
    }
}
