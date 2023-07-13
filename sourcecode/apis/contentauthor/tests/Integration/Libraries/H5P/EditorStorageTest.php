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

    public function test_getAvailableLanguages()
    {
        /** @var H5PLibrary $lib */
        $lib = H5PLibrary::factory()->create();
        $langCodes = H5PLibraryLanguage::factory(2)->create(['library_id' => $lib->id]);

        $es = app(EditorStorage::class);
        $languages = $es->getAvailableLanguages($lib->name, $lib->major_version, $lib->minor_version);

        $this->assertEquals('en', $languages[0]);
        foreach($langCodes as $langCode) {
            $this->assertContains($langCode->language_code, $languages);
        }
    }
}
