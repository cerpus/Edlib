<?php

namespace Tests\Integration\Http\Controllers\Admin;

use App\ContentVersion;
use App\H5PContent;
use App\H5PContentsMetadata;
use App\H5PLibrary;
use App\H5PLibraryLanguage;
use Generator;
use Illuminate\Auth\GenericUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminH5PTranslationTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_libraryTranslation(): void
    {
        Storage::fake();
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);
        $library = H5PLibrary::factory()->create();
        H5PLibraryLanguage::factory()->create(['library_id' => $library->id]);
        $translation = H5PLibraryLanguage::factory()->create([
            'library_id' => $library->id,
            'translation' => '{"data":"DB translation"}',
        ]);

        Storage::put(
            sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $translation->language_code),
            '{"data":"File translation"}',
        );

        $response = $this->withSession(['user' => $user])
            ->get(route('admin.library-translation', [$library, $translation->language_code]))
            ->assertOk()
            ->original;

        $this->assertInstanceOf(View::class, $response);

        $data = $response->getData();
        $this->assertSame($library->id, $data['library']->id);
        $this->assertSame($translation->language_code, $data['languageCode']);
        $this->assertSame($translation->translation, $data['translationDb']->translation);
        $this->assertSame('{"data":"File translation"}', $data['translationFile']);
    }

    public function test_libraryTranslation_UnknownCode(): void
    {
        Storage::fake();
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);
        $library = H5PLibrary::factory()->create();
        $translation = H5PLibraryLanguage::factory()->create([
            'library_id' => $library->id,
            'language_code' => 'nb',
            'translation' => '{"data":"DB translation"}',
        ]);

        Storage::put(
            sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $translation->language_code),
            '{"data":"File translation"}',
        );

        $response = $this->withSession(['user' => $user])
            ->get(route('admin.library-translation', [$library, 'nn']))
            ->assertOk()
            ->original;

        $this->assertInstanceOf(View::class, $response);

        $data = $response->getData();
        $this->assertSame($library->id, $data['library']->id);
        $this->assertSame('nn', $data['languageCode']);
        $this->assertNull($data['translationDb']);
        $this->assertNull($data['translationFile']);
    }

    public function test_libraryTranslationUpdate_Text(): void
    {
        Storage::fake();
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);
        $library = H5PLibrary::factory()->create();
        $unchangedTranslation = H5PLibraryLanguage::factory()->create(['library_id' => $library->id]);
        $translation = H5PLibraryLanguage::factory()->create([
            'library_id' => $library->id,
            'translation' => '{"data":"DB translation"}',
        ]);
        $this->assertDatabaseCount('h5p_libraries_languages', 2);

        Storage::put(
            sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $translation->language_code),
            '{"data":"File translation"}',
        );

        $response = $this->withSession(['user' => $user])
            ->post(
                route('admin.library-translation', [$library, $translation->language_code]),
                ['translation' => '{"data":"Updated DB translation"}'],
            )
            ->assertOk()
            ->original;

        $this->assertInstanceOf(View::class, $response);
        $this->assertDatabaseHas('h5p_libraries_languages', [
            'library_id' => $library->id,
            'language_code' => $translation->language_code,
            'translation' => '{"data":"Updated DB translation"}',
        ]);
        $this->assertDatabaseHas('h5p_libraries_languages', [
            'library_id' => $library->id,
            'language_code' => $unchangedTranslation->language_code,
            'translation' => $unchangedTranslation->translation,
        ]);

        $data = $response->getData();
        $this->assertSame($library->id, $data['library']->id);
        $this->assertSame($translation->language_code, $data['languageCode']);
        $this->assertSame('{"data":"Updated DB translation"}', $data['translationDb']->translation);
        $this->assertSame('{"data":"File translation"}', $data['translationFile']);
        $this->assertTrue($data['messages']->isEmpty());
    }

    public function test_libraryTranslationUpdate_UnkownCode(): void
    {
        Storage::fake();
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);
        $library = H5PLibrary::factory()->create();
        $translation = H5PLibraryLanguage::factory()->create([
            'library_id' => $library->id,
            'language_code' => 'nb',
            'translation' => '{"data":"DB translation"}',
        ]);

        Storage::put(
            sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $translation->language_code),
            '{"data":"File translation"}',
        );

        $response = $this->withSession(['user' => $user])
            ->post(
                route('admin.library-translation', [$library, 'nn']),
                ['translation' => '{"data":"Updated DB translation"}'],
            )
            ->assertOk()
            ->original;

        $this->assertInstanceOf(View::class, $response);
        $this->assertDatabaseMissing('h5p_libraries_languages', [
            'library_id' => $library->id,
            'language_code' => 'nn',
        ]);

        $data = $response->getData();
        $this->assertSame($library->id, $data['library']->id);
        $this->assertSame('nn', $data['languageCode']);
        $this->assertNull($data['translationDb']);
        $this->assertNull($data['translationFile']);
        $this->assertContains('No rows was updated', $data['messages']);
    }

    #[DataProvider('provider_libraryTranslationUpdate_File')]
    public function test_libraryTranslationUpdate_FileError(string $fileContents, ?string $expectedMessage): void
    {
        Storage::fake();
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);
        $library = H5PLibrary::factory()->create();
        $unchangedTranslation = H5PLibraryLanguage::factory()->create(['library_id' => $library->id]);
        $translation = H5PLibraryLanguage::factory()->create([
            'library_id' => $library->id,
            'translation' => '{"data":"DB translation"}',
        ]);

        Storage::put(
            sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $translation->language_code),
            '{"data":"File translation"}',
        );

        $file = UploadedFile::fake()->createWithContent(
            $translation->language_code . '.json',
            $fileContents,
        );

        $response = $this->withSession(['user' => $user])
            ->post(
                route('admin.library-translation', [$library, $translation->language_code]),
                ['translationFile' => $file],
            )
            ->assertOk()
            ->original;

        $storedTranslation = $expectedMessage === null ? $fileContents : $translation->translation;
        $this->assertInstanceOf(View::class, $response);
        $this->assertDatabaseHas('h5p_libraries_languages', [
            'library_id' => $library->id,
            'language_code' => $translation->language_code,
            'translation' => $storedTranslation,
        ]);

        $this->assertDatabaseHas('h5p_libraries_languages', [
            'library_id' => $library->id,
            'language_code' => $unchangedTranslation->language_code,
            'translation' => $unchangedTranslation->translation,
        ]);

        $data = $response->getData();
        $this->assertSame($library->id, $data['library']->id);
        $this->assertSame($translation->language_code, $data['languageCode']);
        $this->assertSame($storedTranslation, $data['translationDb']->translation);
        $this->assertSame('{"data":"File translation"}', $data['translationFile']);
        if ($expectedMessage !== null) {
            $this->assertContains($expectedMessage, $data['messages']);
        } else {
            $this->assertTrue($data['messages']->isEmpty());
        }
    }

    public static function provider_libraryTranslationUpdate_File(): Generator
    {
        yield 'valid file' => ['{"data":"Upload translation"}', null];
        yield 'empty file' => ['', 'Content was empty'];
        yield 'invalid file' => ['Not JSON', 'Syntax error'];
    }

    public function test_contentRefresh(): void
    {
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);

        $library = H5PLibrary::factory()->create();

        // A new resource, will be counted
        $content_1 = H5PContent::factory()->create([
            'library_id' => $library->id,
            'language_iso_639_3' => 'nob',
            'version_id' => $this->faker->uuid,
        ]);
        ContentVersion::factory()->create([
            'id' => $content_1->version_id,
            'content_id' => $content_1->id,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
        ]);
        H5PContentsMetadata::factory()->create([
            'content_id' => $content_1->id,
            'default_language' => 'nb',
        ]);

        // Not counted since $content_3 is an update of this
        $content_2 = H5PContent::factory()->create([
            'library_id' => $library->id,
            'language_iso_639_3' => 'nob',
            'version_id' => $this->faker->uuid,
        ]);
        ContentVersion::factory()->create([
            'id' => $content_2->version_id,
            'content_id' => $content_2->id,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
        ]);
        H5PContentsMetadata::factory()->create([
            'content_id' => $content_2->id,
            'default_language' => 'nb',
        ]);

        // An update of $content_2. Will be counted and prevents $content_2 from being counted
        $content_3 = H5PContent::factory()->create([
            'library_id' => $library->id,
            'language_iso_639_3' => 'nob',
            'version_id' => $this->faker->uuid,
        ]);
        ContentVersion::factory()->create([
            'id' => $content_3->version_id,
            'parent_id' => $content_2->version_id,
            'content_id' => $content_3->id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);
        H5PContentsMetadata::factory()->create([
            'content_id' => $content_3->id,
            'default_language' => 'nb',
        ]);

        // A copy of $content_1. Will be counted, and should not prevent $content_1 from being counted
        $content_4 = H5PContent::factory()->create([
            'library_id' => $library->id,
            'language_iso_639_3' => 'nob',
            'version_id' => $this->faker->uuid,
        ]);
        ContentVersion::factory()->create([
            'id' => $content_4->version_id,
            'parent_id' => $content_1->version_id,
            'content_id' => $content_4->id,
            'version_purpose' => ContentVersion::PURPOSE_COPY,
        ]);
        H5PContentsMetadata::factory()->create([
            'content_id' => $content_4->id,
            'default_language' => 'nb',
        ]);

        $response = $this->withSession(['user' => $user])
            ->get(
                route('admin.library-transation-content', [$library, 'nb']),
            )
            ->assertOk()
            ->getOriginalContent();

        $data = $response->getData();
        $this->assertSame(3, $data['contentCount']);
        $this->assertCount(7, $data['scripts']);

        $this->assertSame('H5P.Foobar 1.2.3', $data['libraryName']);
        $this->assertArrayHasKey('ajaxPath', $data['jsConfig']);
        $this->assertArrayHasKey('endpoint', $data['jsConfig']);
        $this->assertSame($library->id, $data['jsConfig']['libraryId']);
        $this->assertSame('H5P.Foobar 1.2', $data['jsConfig']['library']);
        $this->assertSame('nb', $data['jsConfig']['locale']);
    }

    public function test_contentUpdate_getFirstBatch(): void
    {
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);

        $library = H5PLibrary::factory()->create();

        // A new resource, will be included
        $content_1 = H5PContent::factory()->create([
            'library_id' => $library->id,
            'language_iso_639_3' => 'nob',
            'version_id' => $this->faker->uuid,
            'parameters' => '{"text":"First"}',
        ]);
        ContentVersion::factory()->create([
            'id' => $content_1->version_id,
            'content_id' => $content_1->id,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
        ]);
        H5PContentsMetadata::factory()->create([
            'content_id' => $content_1->id,
            'default_language' => 'nb',
        ]);

        // Not included since $content_3 is an update of this
        $content_2 = H5PContent::factory()->create([
            'library_id' => $library->id,
            'language_iso_639_3' => 'nob',
            'version_id' => $this->faker->uuid,
            'parameters' => '{"text":"Second"}',
        ]);
        ContentVersion::factory()->create([
            'id' => $content_2->version_id,
            'content_id' => $content_2->id,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
        ]);
        H5PContentsMetadata::factory()->create([
            'content_id' => $content_2->id,
            'default_language' => 'nb',
        ]);

        // An update of $content_2. Will be included and prevents $content_2 from being included
        $content_3 = H5PContent::factory()->create([
            'library_id' => $library->id,
            'language_iso_639_3' => 'nob',
            'version_id' => $this->faker->uuid,
            'parameters' => '{"text":"Third"}',
        ]);
        ContentVersion::factory()->create([
            'id' => $content_3->version_id,
            'parent_id' => $content_2->version_id,
            'content_id' => $content_3->id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);
        H5PContentsMetadata::factory()->create([
            'content_id' => $content_3->id,
            'default_language' => 'nb',
        ]);

        // A copy of $content_1. Will be included, and should not prevent $content_1 from being included
        $content_4 = H5PContent::factory()->create([
            'library_id' => $library->id,
            'language_iso_639_3' => 'nob',
            'version_id' => $this->faker->uuid,
            'parameters' => '{"text":"Fourth"}',
        ]);
        ContentVersion::factory()->create([
            'id' => $content_4->version_id,
            'parent_id' => $content_1->version_id,
            'content_id' => $content_4->id,
            'version_purpose' => ContentVersion::PURPOSE_COPY,
        ]);
        H5PContentsMetadata::factory()->create([
            'content_id' => $content_4->id,
            'default_language' => 'nb',
        ]);

        $response = $this->withSession(['user' => $user])
            ->post(
                route('admin.library-transation-content-update', [$library, 'nb']),
                [
                    'libraryId' => $library->id,
                    'locale' => 'nb',
                ],
            )
            ->assertOk()
            ->json();

        $this->assertCount(3, $response['params']);
        $this->assertSame(3, $response['left']);
        $this->assertIsArray($response['messages']);
        $this->assertCount(0, $response['messages']);

        $this->assertSame($content_1->id, $response['params'][0]['id']);
        $this->assertSame($content_3->id, $response['params'][1]['id']);
        $this->assertSame($content_4->id, $response['params'][2]['id']);
    }

    public function test_contentUpdate_updateAndSecondBatch(): void
    {
        $ownerId = $this->faker->uuid;

        $admin = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);

        $library = H5PLibrary::factory()->create();

        // A new resource, will be included
        $content_1 = H5PContent::factory()->create([
            'library_id' => $library->id,
            'language_iso_639_3' => 'nob',
            'version_id' => $this->faker->uuid,
            'parameters' => '{"text":"First"}',
            'filtered' => '{"text":"First"}',
            'user_id' => $ownerId,
        ]);
        ContentVersion::factory()->create([
            'id' => $content_1->version_id,
            'content_id' => $content_1->id,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
        ]);
        H5PContentsMetadata::factory()->create([
            'content_id' => $content_1->id,
            'default_language' => 'nb',
        ]);

        // Not included since $content_3 is an update of this
        $content_2 = H5PContent::factory()->create([
            'library_id' => $library->id,
            'language_iso_639_3' => 'nob',
            'version_id' => $this->faker->uuid,
            'parameters' => '{"text":"Second"}',
            'filtered' => '{"text":"Second"}',
            'user_id' => $ownerId,
        ]);
        ContentVersion::factory()->create([
            'id' => $content_2->version_id,
            'content_id' => $content_2->id,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
        ]);
        H5PContentsMetadata::factory()->create([
            'content_id' => $content_2->id,
            'default_language' => 'nb',
        ]);

        // An update of $content_2. Will be included and prevents $content_2 from being included
        $content_3 = H5PContent::factory()->create([
            'library_id' => $library->id,
            'language_iso_639_3' => 'nob',
            'version_id' => $this->faker->uuid,
            'parameters' => '{"text":"Third"}',
            'filtered' => '{"text":"Third"}',
            'user_id' => $ownerId,
        ]);
        ContentVersion::factory()->create([
            'id' => $content_3->version_id,
            'parent_id' => $content_2->version_id,
            'content_id' => $content_3->id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);
        H5PContentsMetadata::factory()->create([
            'content_id' => $content_3->id,
            'default_language' => 'nb',
        ]);

        // A copy of $content_1. Will be included, and should not prevent $content_1 from being included
        $content_4 = H5PContent::factory()->create([
            'library_id' => $library->id,
            'language_iso_639_3' => 'nob',
            'version_id' => $this->faker->uuid,
            'parameters' => '{"text":"Fourth"}',
            'filtered' => '{"text":"Fourth"}',
            'user_id' => $ownerId,
        ]);
        ContentVersion::factory()->create([
            'id' => $content_4->version_id,
            'parent_id' => $content_1->version_id,
            'content_id' => $content_4->id,
            'version_purpose' => ContentVersion::PURPOSE_COPY,
        ]);
        H5PContentsMetadata::factory()->create([
            'content_id' => $content_4->id,
            'default_language' => 'nb',
        ]);

        // Sending 1 updated: $content_1
        // and 1 unchanged: $content_3
        // Should receive 1 to process: $content_4
        $response = $this->withSession(['user' => $admin])
            ->post(
                route('admin.library-transation-content-update', [$library, 'nb']),
                [
                    'libraryId' => $library->id,
                    'locale' => 'nb',
                    'processed' => [
                        $content_1->id => '{"text":"Updated"}',
                        $content_3->id => $content_3->parameters,
                    ],
                ],
            )
            ->assertOk()
            ->json();

        $this->assertCount(1, $response['params']);
        $this->assertSame(1, $response['left']);
        $this->assertIsArray($response['messages']);
        $this->assertCount(3, $response['messages']);

        $this->assertSame($content_4->id, $response['params'][0]['id']);
        $this->assertSame('Content ' . $content_1->id . ' updated', $response['messages'][0]);
        $this->assertSame('Content ' . $content_3->id . ' not changed', $response['messages'][1]);
        $this->assertSame('Content updated/unchanged/failed: 1 / 1 / 0', $response['messages'][2]);

        $content_1_fresh = $content_1->fresh();
        $this->assertNotSame($content_1_fresh->version_id, $content_1->version_id);
        $this->assertNotSame($content_1_fresh->parameters, $content_1->parameters);
        $this->assertSame('{"text":"Updated"}', $content_1_fresh->parameters);
        $this->assertEmpty($content_1_fresh->filtered);
        $this->assertDatabaseHas('content_versions', [
            'id' => $content_1_fresh->version_id,
            'content_id' => $content_1->id,
            'parent_id' => $content_1->version_id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);

        $content_3_fresh = $content_3->fresh();
        $this->assertSame($content_3->version_id, $content_3_fresh->version_id);
        $this->assertSame($content_3->parameters, $content_3_fresh->parameters);
        $this->assertSame($content_3->parameters, $content_3_fresh->filtered);
    }
}
