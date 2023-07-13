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
        foreach ($langCodes as $langCode) {
            $this->assertContains($langCode->language_code, $languages);
        }
    }

    /** @dataProvider provider_getLibraries */
    public function test_getLibraries(?array $params): void
    {
        /** @var H5PLibrary $lib_1 */
        $lib_1 = H5PLibrary::factory()->create([
            'semantics' => '{"value":"not null"}',
            'restricted' => 0,
        ]);
        /** @var H5PLibrary $lib_2 */
        $lib_2 = H5PLibrary::factory()->create([
            'major_version' => 2,
            'semantics' => '{"value":"not null"}',
            'restricted' => 1,
        ]);
        /** @var H5PLibrary $lib_3 */
        $lib_3 = H5PLibrary::factory()->create([
            'name' => 'H5P.Toolbar',
            'semantics' => '{"value":"not null"}',
        ]);

        $es = app(EditorStorage::class);
        $data = $es->getLibraries($params);

        $this->assertIsArray($data);
        $this->assertCount(3, $data);

        $this->assertObjectHasAttribute('id', $data[0]);
        $this->assertObjectHasAttribute('name', $data[0]);
        $this->assertObjectHasAttribute('title', $data[0]);
        $this->assertObjectHasAttribute('tutorialUrl', $data[0]);
        $this->assertObjectHasAttribute('restricted', $data[0]);
        $this->assertObjectHasAttribute('metadataSettings', $data[0]);
        $this->assertObjectHasAttribute('majorVersion', $data[0]);
        $this->assertObjectHasAttribute('minorVersion', $data[0]);
        $this->assertObjectHasAttribute('uberName', $data[0]);

        $this->assertTrue($data[0]->restricted);
        $this->assertFalse($data[1]->restricted);

        $this->assertEquals($lib_2->id, $data[0]->id);
        $this->assertEquals('H5P.Foobar 2.2', $data[0]->uberName);

        $this->assertEquals($lib_1->id, $data[1]->id);
        $this->assertEquals('H5P.Foobar 1.2', $data[1]->uberName);

        $this->assertEquals($lib_3->id, $data[2]->id);
        $this->assertEquals('H5P.Foobar 1.2', $data[1]->uberName);
    }

    public function provider_getLibraries(): \Generator
    {
        yield 'null' => [null];
        yield 'withLibraries' => [
            [
                (object)[
                    'name' => 'H5P.Foobar',
                    'majorVersion' => 2,
                    'minorVersion' => 2,
                ],
                (object)[
                    'name' => 'H5P.Foobar',
                    'majorVersion' => 1,
                    'minorVersion' => 2,
                ],
                (object)[
                    'name' => 'H5P.Toolbar',
                    'majorVersion' => 1,
                    'minorVersion' => 2,
                ],
            ]
        ];
    }
}
