<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PLibrary;
use App\H5PLibraryLanguage;
use App\Libraries\H5P\EditorStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_getLibraries_forLibrary(): void
    {
        $library = H5PLibrary::factory()->create(['semantics' => 'something']);
        $libraries = (object) [
            'uberName' => $library->getLibraryString(false),
            'name' => $library->name,
            'majorVersion' => $library->major_version,
            'minorVersion' => $library->minor_version,
        ];

        $core = $this->createMock(\H5PCore::class);
        $this->instance(\H5PCore::class, $core);

        $editorStorage = app(EditorStorage::class);
        $ret = $editorStorage->getLibraries([$libraries]);

        $this->assertCount(1, $ret);
        $this->assertSame($library->id, $ret[0]->id);
        $this->assertSame('H5P.Foobar 1.2', $ret[0]->uberName);
    }

    public function test_getLibrary_allLibraries(): void
    {
        $lib1 = H5PLibrary::factory()->create(['semantics' => 'something']);
        $lib2 = H5PLibrary::factory()->create(['name' => 'H5P.Headphones', 'semantics' => 'something']);

        $core = $this->createMock(\H5PCore::class);
        $this->instance(\H5PCore::class, $core);

        $editorStorage = app(EditorStorage::class);
        $ret = $editorStorage->getLibraries();

        $this->assertCount(2, $ret);
        $this->assertSame($lib1->id, $ret[0]->id);
        $this->assertSame($lib2->id, $ret[1]->id);

        $this->assertSame('H5P.Foobar 1.2', $ret[0]->uberName);
        $this->assertSame('H5P.Headphones 1.2', $ret[1]->uberName);
    }

    public function test_getAvailableLanguages()
    {
        $lib = H5PLibrary::factory()->create();
        $langCodes = H5PLibraryLanguage::factory(2)->create(['library_id' => $lib->id]);

        $es = app(EditorStorage::class);
        $languages = $es->getAvailableLanguages($lib->name, $lib->major_version, $lib->minor_version);

        $this->assertEquals('en', $languages[0]);
        foreach ($langCodes as $langCode) {
            $this->assertContains($langCode->language_code, $languages);
        }
    }

    public function test_getLanguage(): void
    {
        $lib = H5PLibrary::factory()->create();
        H5PLibraryLanguage::factory()->create([
            'library_id' => $lib->id,
        ]);
        $langCode = H5PLibraryLanguage::factory()->create([
            'library_id' => $lib->id,
            'translation' => '{"test":"success"}',
        ]);

        $es = app(EditorStorage::class);
        $result = $es->getLanguage(
            $lib->name,
            $lib->major_version,
            $lib->minor_version,
            $langCode->language_code
        );
        $translation = json_decode($result, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('success', $translation['test']);
    }
}
