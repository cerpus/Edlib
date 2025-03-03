<?php

declare(strict_types=1);

namespace Tests\Integration\Libraries\H5P;

use App\H5PLibrary;
use App\H5PLibraryLanguage;
use App\Libraries\H5P\EditorAjax;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorAjaxTest extends TestCase
{
    use RefreshDatabase;

    public function test_getTranslations(): void
    {
        $libFoo = H5PLibrary::factory()->create();
        $libTest = H5PLibrary::factory()->create([
            'name' => 'H5P.UnitTest',
            'major_version' => 3,
            'minor_version' => 14,
            'patch_version' => 42,
            'patch_version_in_folder_name' => 1,
        ]);

        H5PLibraryLanguage::factory()->create([
            'library_id' => $libFoo->id,
            'language_code' => 'nb',
            'translation' => json_encode(['lib' => $libFoo->getLibraryString(false), 'lang' => 'nb']),
        ]);

        H5PLibraryLanguage::factory()->create([
            'library_id' => $libTest->id,
            'language_code' => 'nb',
            'translation' => json_encode(['lib' => $libTest->getLibraryString(false), 'lang' => 'nb']),
        ]);

        H5PLibraryLanguage::factory()->create([
            'library_id' => $libFoo->id,
            'language_code' => 'nn',
            'translation' => json_encode(['lib' => $libFoo->getLibraryString(false), 'lang' => 'nn']),
        ]);

        H5PLibraryLanguage::factory()->create([
            'library_id' => $libTest->id,
            'language_code' => 'en',
            'translation' => json_encode(['lib' => $libTest->getLibraryString(false), 'lang' => 'en']),
        ]);

        $translations = (new EditorAjax())->getTranslations(
            [
                $libFoo->getLibraryString(false),
                $libTest->getLibraryString(false),
            ],
            'nb',
        );

        $this->assertIsArray($translations);

        $this->assertArrayHasKey($libFoo->getLibraryString(false), $translations);
        $data = json_decode($translations[$libFoo->getLibraryString(false)], true, JSON_THROW_ON_ERROR);
        $this->assertSame($libFoo->getLibraryString(false), $data['lib']);
        $this->assertSame('nb', $data['lang']);

        $this->assertArrayHasKey($libFoo->getLibraryString(false), $translations);
        $data = json_decode($translations[$libTest->getLibraryString(false)], true, JSON_THROW_ON_ERROR);
        $this->assertSame($libTest->getLibraryString(false), $data['lib']);
        $this->assertSame('nb', $data['lang']);
    }
}
